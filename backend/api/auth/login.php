<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
session_start();

$data = json_decode(file_get_contents("php://input"), true);
$email = $data["email"] ?? "";
$password = $data["password"] ?? "";

if (!is_string($email) || !is_string($password)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid login details"
	]);
	exit;
}

$email = strtolower(trim($email));

$stmt = $conn->prepare(
    "SELECT
        u.user_id,
        u.username,
        u.email,
        u.password,
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
     WHERE u.email = ?"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("s", $email);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$result = $stmt->get_result();
$stmt->close();
$user = $result->fetch_assoc();

if (!$user || !password_verify($password, $user["password"])) {
	http_response_code(401);

	echo json_encode([
		"success" => false,
		"message" => "Invalid email or password"
	]);

	exit;
}

if ($user["status"] != 1 || $user["is_suspended"] == 1) {
    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "Your account is disabled or suspended."
    ]);
    exit;
}

session_regenerate_id(true);
$_SESSION["user_id"] = $user["user_id"];

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