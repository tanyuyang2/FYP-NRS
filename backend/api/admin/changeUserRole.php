<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireAdmin.php";

$data = json_decode(file_get_contents("php://input"), true);
$targetId = $data["user_id"] ?? null;
$roleId = $data["role_id"] ?? null;

if (!$targetId || !is_numeric($targetId)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid user ID"
	]);
	exit;
}

if (!in_array($roleId, ["1", "2"], true)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid role"
	]);
	exit;
}

if ($targetId == $userId) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "You cannot change your own role"
	]);
	exit;
}

$stmt = $conn->prepare(
	"SELECT user_id
	 FROM users
	 WHERE user_id = ?"
);

if (!$stmt) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->bind_param("i", $targetId);

if (!$stmt->execute()) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows === 0) {
	http_response_code(404);
	echo json_encode([
		"success" => false,
		"message" => "User not found"
	]);
	exit;
}

$stmt = $conn->prepare(
	"UPDATE users
	 SET role_id = ?
	 WHERE user_id = ?"
);

if (!$stmt) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->bind_param("ii", $roleId, $targetId);

if (!$stmt->execute()) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->close();

echo json_encode([
	"success" => true,
	"message" => "User role updated",
	"role_id" => $roleId
]);