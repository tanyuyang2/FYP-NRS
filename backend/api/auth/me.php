<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
session_start();

if (!isset($_SESSION["user_id"])) {
	echo json_encode([
		"success" => false,
		"user" => null
	]);
	exit;
}

$userId = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT
        u.user_id,
        u.username,
        u.email,
        u.profile_picture,
        u.bio,
        u.role_id,
        u.status,
        u.suspended_until,
		u.coin,
		COALESCE(u.suspended_until > NOW(), 0) AS is_suspended,
        (
            SELECT c.image_url
            FROM cosmetic c
            JOIN user_equipped_cosmetic equipped ON c.cosmetic_id = equipped.cosmetic_id AND c.cosmetic_type_id = equipped.cosmetic_type_id
            WHERE equipped.user_id = u.user_id AND c.cosmetic_type_id = 1
        ) AS profile_frame_url
     FROM users u
     WHERE u.user_id = ?"
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
	session_unset();
    session_destroy();
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "User not found"
    ]);
    exit;
}

if ($user["status"] != 1 || $user["is_suspended"] == 1) {
    session_unset();
    session_destroy();
    http_response_code(403);

    echo json_encode([
        "success" => false,
        "user" => null,
        "message" => "Your account is disabled or suspended."
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "user" => [
        "user_id" => $user["user_id"],
        "username" => $user["username"],
        "email" => $user["email"],
        "profile_picture" => $user["profile_picture"],
		"bio" => $user["bio"],
		"role_id" => $user["role_id"],
		"profile_frame_url" => $user["profile_frame_url"],
		"coin" => $user["coin"]
    ]
]);