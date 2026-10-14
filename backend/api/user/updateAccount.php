<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireUser.php";

$data = json_decode(file_get_contents("php://input"), true);
$userId = $_SESSION["user_id"];
$email = $data["email"] ?? "";
$currentPassword = $data["current_password"] ?? "";
$newPassword = $data["new_password"] ?? "";

if (!is_string($email) || !is_string($currentPassword) || !is_string($newPassword)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid account details"
	]);
	exit;
}

$email = strtolower(trim($email));

if ($email === "" || $currentPassword === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Email and current password are required"
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid email format"
    ]);
    exit;
}

if ($newPassword !== "" && !preg_match("/^(?!\s)(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\S$).{8,}$/u", $newPassword)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Password must be at least 8 characters, include an uppercase letter, lowercase letter, and number, and cannot start or end with spaces"
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT email, password
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

if (!password_verify($currentPassword, $user["password"])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Current password is incorrect"
    ]);
    exit;
}

if ($email !== $user["email"]) {
    $verifiedEmail = $_SESSION["verified_change_email"] ?? null;

    if (
        !$verifiedEmail ||
        $verifiedEmail["user_id"] != $userId ||
        $verifiedEmail["email"] !== $email
    ) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Please verify your new email before saving"
        ]);
        exit;
    }
}

if ($newPassword !== "") {
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "UPDATE users
         SET email = ?, password = ?
         WHERE user_id = ?"
    );
} else {
    $stmt = $conn->prepare(
        "UPDATE users
         SET email = ?
         WHERE user_id = ?"
    );
}

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

if ($newPassword !== "") {
    $stmt->bind_param(
        "ssi",
        $email,
        $hashedPassword,
        $userId
    );
} else {
    $stmt->bind_param(
        "si",
        $email,
        $userId
    );
}

if (!$stmt->execute()) {
    if ($stmt->errno === 1062) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Email already exists"
        ]);
        exit;
    }	
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->close();

unset($_SESSION["verified_change_email"]);

echo json_encode([
    "success" => true,
    "message" => "Account updated successfully"
]);