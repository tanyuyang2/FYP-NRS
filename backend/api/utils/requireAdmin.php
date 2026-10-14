<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "You must be logged in"
    ]);
    exit;
}

$userId = $_SESSION["user_id"];

$stmt = $conn->prepare(
	"SELECT role_id, status, COALESCE(suspended_until > NOW(), 0) AS is_suspended
	 FROM users
	 WHERE user_id = ?"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("i", $userId);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$result = $stmt->get_result();
$stmt->close();
$user = $result->fetch_assoc();

if (!$user) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "User not found"
    ]);
    exit;
}

if ($user["role_id"] != 2 || $user["status"] != 1 || $user["is_suspended"] == 1) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "You do not have permission"
    ]);
    exit;
}