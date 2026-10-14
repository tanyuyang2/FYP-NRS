<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireAdmin.php";

$data = json_decode(file_get_contents("php://input"), true);

$articleId = $data["article_id"] ?? null;
$commentId = $data["comment_id"] ?? null;
$duration = $data["duration"] ?? null;
$reason = $data["reason"] ?? "";
$notes = $data["notes"] ?? "";
$isArticle = $articleId !== null;
$targetId = $isArticle ? $articleId : $commentId;
$isBan = $duration === "ban";
$action = $isBan ? "ban" : "suspension";

if (!is_string($reason) || !is_string($notes)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid reason or notes"
	]);
	exit;
}

$reason = trim($reason);
$notes = trim($notes);
$notes = $notes === "" ? null : $notes;

if (($articleId === null) === ($commentId === null)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Provide an article or comment ID"
	]);
	exit;
}

if (!$targetId || !is_numeric($targetId)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid article or comment ID"
	]);
	exit;
}

if (!in_array($duration, ["7", "14", "30", "ban"], true)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid suspension duration"
	]);
	exit;
}

if ($reason === "") {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Reason is required"
	]);
	exit;
}

if (mb_strlen($reason) > 2000) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Reason cannot exceed 2000 characters"
	]);
	exit;
}

if (!$conn->begin_transaction()) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

if ($isArticle) {
	$stmt = $conn->prepare(
		"SELECT author_id
		 FROM article
		 WHERE article_id = ? AND status <> 4"
	);
} else {
	$stmt = $conn->prepare(
		"SELECT user_id AS author_id
		 FROM comment
		 WHERE comment_id = ? AND status <> 4"
	);
}

if (!$stmt) {
	$conn->rollback();
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->bind_param("i", $targetId);

if (!$stmt->execute()) {
	$conn->rollback();
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$result = $stmt->get_result();
$stmt->close();
$content = $result->fetch_assoc();

if (!$content) {
	$conn->rollback();
	http_response_code(404);
	echo json_encode([
		"success" => false,
		"message" => "Content not found or already removed by an admin"
	]);
	exit;
}

$authorId = $content["author_id"];

$stmt = $conn->prepare(
	"INSERT INTO report_resolution (
		action,
		admin_id,
		notes
	 )
	 VALUES (?, ?, ?)"
);

if (!$stmt) {
	$conn->rollback();
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->bind_param("sis", $action, $userId, $notes);

if (!$stmt->execute()) {
	$conn->rollback();
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$resolutionId = $stmt->insert_id;
$stmt->close();

if ($isArticle) {
	$stmt = $conn->prepare(
		"UPDATE report
		 SET resolution_id = ?
		 WHERE article_id = ? AND resolution_id IS NULL"
	);
} else {
	$stmt = $conn->prepare(
		"UPDATE report
		 SET resolution_id = ?
		 WHERE comment_id = ? AND resolution_id IS NULL"
	);
}

if (!$stmt) {
	$conn->rollback();
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->bind_param("ii", $resolutionId, $targetId);

if (!$stmt->execute()) {
	$conn->rollback();
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$affectedRows = $stmt->affected_rows;
$stmt->close();

if ($affectedRows === 0) {
	$conn->rollback();
	http_response_code(409);
	echo json_encode([
		"success" => false,
		"message" => "This report is no longer pending or could not be found"
	]);
	exit;
}

if ($isArticle) {
	$stmt = $conn->prepare(
		"UPDATE article
		 SET status = 4
		 WHERE article_id = ?"
	);
} else {
	$stmt = $conn->prepare(
		"UPDATE comment
		 SET status = 4
		 WHERE comment_id = ?"
	);
}

if (!$stmt) {
	$conn->rollback();
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->bind_param("i", $targetId);

if (!$stmt->execute()) {
	$conn->rollback();
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->close();

if ($isBan) {
	$stmt = $conn->prepare(
		"UPDATE users
		 SET status = 4
		 WHERE user_id = ?"
	);
} else {
	$days = $duration;
	$stmt = $conn->prepare(
		"UPDATE users
		 SET suspended_until = GREATEST(COALESCE(suspended_until, NOW()), NOW() + INTERVAL ? DAY)
		 WHERE user_id = ?"
	);
}

if (!$stmt) {
	$conn->rollback();
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

if ($isBan) {
	$stmt->bind_param("i", $authorId);
} else {
	$stmt->bind_param("ii", $days, $authorId);
}

if (!$stmt->execute()) {
	$conn->rollback();
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->close();

$stmt = $conn->prepare(
	"INSERT INTO user_moderation_history (
		user_id,
		admin_id,
		action,
		reason,
		suspended_until
	 )
	 SELECT
		user_id,
		?,
		?,
		?,
		CASE WHEN ? = 'suspension' THEN suspended_until ELSE NULL END
	 FROM users
	 WHERE user_id = ?"
);

if (!$stmt) {
	$conn->rollback();
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->bind_param("isssi", $userId, $action, $reason, $action, $authorId);

if (!$stmt->execute()) {
	$conn->rollback();
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->close();

if (!$isBan) {
	$contentType = $isArticle ? "article" : "comment";
	$message = "suspended your account and removed your $contentType. Reason: $reason\n\nResolution ID: #$resolutionId.";

	$stmt = $conn->prepare(
		"INSERT INTO notification (
			user_id,
			actor_id,
			article_id,
			type,
			message
		 )
		 VALUES (?, ?, NULL, 4, ?)"
	);

	if (!$stmt) {
		$conn->rollback();
		http_response_code(500);
		echo json_encode(["success" => false, "message" => "Server error"]);
		exit;
	}

	$stmt->bind_param("iis", $authorId, $userId, $message);

	if (!$stmt->execute()) {
		$conn->rollback();
		http_response_code(500);
		echo json_encode(["success" => false, "message" => "Server error"]);
		exit;
	}

	$stmt->close();
}

if (!$conn->commit()) {
	$conn->rollback();
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

echo json_encode([
	"success" => true,
	"message" => $isBan
		? "User banned and reported content removed"
		: "User suspended and reported content removed"
]);
