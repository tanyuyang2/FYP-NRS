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

$sessionUserId = $_SESSION["user_id"];

$stmt = $conn->prepare(
	"SELECT status, COALESCE(suspended_until > NOW(), 0) AS is_suspended
	 FROM users
	 WHERE user_id = ?"
);

if (!$stmt) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->bind_param("i", $sessionUserId);

if (!$stmt->execute()) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$result = $stmt->get_result();
$stmt->close();
$sessionUser = $result->fetch_assoc();

if (!$sessionUser) {
	session_unset();
	session_destroy();

	http_response_code(401);
	echo json_encode([
		"success" => false,
		"message" => "User not found"
	]);
	exit;
}

if ($sessionUser["status"] != 1 || $sessionUser["is_suspended"] == 1) {
	session_unset();
	session_destroy();

	http_response_code(403);
	echo json_encode([
		"success" => false,
		"message" => "Your account is disabled or suspended."
	]);
	exit;
}