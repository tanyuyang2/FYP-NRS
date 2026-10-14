<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireAdmin.php";

$data = json_decode(file_get_contents("php://input"), true);

$articleId = $data["article_id"] ?? null;
$commentId = $data["comment_id"] ?? null;
$notes = $data["notes"] ?? "";
$isArticle = $articleId !== null;
$targetId = $isArticle ? $articleId : $commentId;

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

if (!is_string($notes)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid notes"
	]);
	exit;
}

$notes = trim($notes);
$notes = $notes === "" ? null : $notes;

if (!$conn->begin_transaction()) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt = $conn->prepare(
	"INSERT INTO report_resolution (
		action,
		admin_id,
		notes
	 )
	 VALUES ('dismissed', ?, ?)"
);

if (!$stmt) {
	$conn->rollback();
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->bind_param("is", $userId, $notes);

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

if (!$conn->commit()) {
	$conn->rollback();
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

echo json_encode([
	"success" => true,
	"message" => "Report dismissed"
]);
