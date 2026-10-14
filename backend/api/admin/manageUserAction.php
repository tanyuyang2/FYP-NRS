<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireAdmin.php";

$data = json_decode(file_get_contents("php://input"), true);
$targetId = $data["user_id"] ?? null;
$action = $data["action"] ?? null;
$reason = $data["reason"] ?? "";
$duration = $data["duration"] ?? "";

if (!$targetId || !is_numeric($targetId)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid user ID"
	]);
	exit;
}

if (!in_array($action, ["warning", "suspension", "ban", "lift_suspension", "unban"], true)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid account action"
	]);
	exit;
}

if ($targetId == $userId) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "You cannot perform this action on your own account"
	]);
	exit;
}

if (in_array($action, ["warning", "suspension", "ban"], true)) {
	if (!is_string($reason)) {
		http_response_code(400);
		echo json_encode([
			"success" => false,
			"message" => "Invalid reason"
		]);
		exit;
	}

	$reason = trim($reason);

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
}

if ($action === "suspension" && !in_array($duration, ["7", "14", "30"], true)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid suspension duration"
	]);
	exit;
}

if (!$conn->begin_transaction()) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt = $conn->prepare(
	"SELECT status, COALESCE(suspended_until > NOW(), 0) AS is_suspended
	 FROM users
	 WHERE user_id = ?
	 FOR UPDATE"
);

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
$target = $result->fetch_assoc();

if (!$target) {
	$conn->rollback();
	http_response_code(404);
	echo json_encode([
		"success" => false,
		"message" => "User not found"
	]);
	exit;
}

if (($action === "suspension" || $action === "ban") && $target["status"] == 4) {
	$conn->rollback();
	http_response_code(409);
	echo json_encode([
		"success" => false,
		"message" => "This user is already banned"
	]);
	exit;
}

if ($action === "lift_suspension" && $target["is_suspended"] != 1) {
	$conn->rollback();
	http_response_code(409);
	echo json_encode([
		"success" => false,
		"message" => "This user is not suspended"
	]);
	exit;
}

if ($action === "unban" && $target["status"] != 4) {
	$conn->rollback();
	http_response_code(409);
	echo json_encode([
		"success" => false,
		"message" => "This user is not banned"
	]);
	exit;
}

if ($action !== "warning") {
	switch ($action) {
		case "suspension":
			$stmt = $conn->prepare(
				"UPDATE users
				 SET suspended_until = GREATEST(COALESCE(suspended_until, NOW()), NOW() + INTERVAL ? DAY)
				 WHERE user_id = ?"
			);
			break;

		case "ban":
			$stmt = $conn->prepare(
				"UPDATE users
				 SET status = 4
				 WHERE user_id = ?"
			);
			break;

		case "lift_suspension":
			$stmt = $conn->prepare(
				"UPDATE users
				 SET suspended_until = NULL
				 WHERE user_id = ?"
			);
			break;

		case "unban":
			$stmt = $conn->prepare(
				"UPDATE users
				 SET status = 1
				 WHERE user_id = ?"
			);
			break;
	}

	if (!$stmt) {
		$conn->rollback();
		http_response_code(500);
		echo json_encode(["success" => false, "message" => "Server error"]);
		exit;
	}

	if ($action === "suspension") {
		$stmt->bind_param("ii", $duration, $targetId);
	} else {
		$stmt->bind_param("i", $targetId);
	}

	if (!$stmt->execute()) {
		$conn->rollback();
		http_response_code(500);
		echo json_encode(["success" => false, "message" => "Server error"]);
		exit;
	}

	$stmt->close();
}

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

$stmt->bind_param("isssi", $userId, $action, $reason, $action, $targetId);

if (!$stmt->execute()) {
	$conn->rollback();
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$historyId = $stmt->insert_id;
$stmt->close();

if ($action === "warning" || $action === "suspension") {
	$type = $action === "warning" ? 3 : 4;
	$message = $action === "warning"
		? "issued you a warning. Reason: $reason\n\nModeration ID: #$historyId."
		: "suspended your account. Reason: $reason\n\nModeration ID: #$historyId.";

	$stmt = $conn->prepare(
		"INSERT INTO notification (
			user_id,
			actor_id,
			article_id,
			type,
			message
		 )
		 VALUES (?, ?, NULL, ?, ?)"
	);

	if (!$stmt) {
		$conn->rollback();
		http_response_code(500);
		echo json_encode(["success" => false, "message" => "Server error"]);
		exit;
	}

	$stmt->bind_param("iiis", $targetId, $userId, $type, $message);

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

switch ($action) {
	case "warning":
		$message = "Warning issued";
		break;
	case "suspension":
		$message = "User suspended";
		break;
	case "ban":
		$message = "User banned";
		break;
	case "lift_suspension":
		$message = "Suspension lifted";
		break;
	case "unban":
		$message = "User unbanned";
		break;
}

echo json_encode([
	"success" => true,
	"message" => $message
]);
