<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";

session_start();

$data = json_decode(file_get_contents("php://input"), true);
$username = $data["username"] ?? "";
$email = $data["email"] ?? "";
$password = $data["password"] ?? "";

if (!is_string($username) || !is_string($email) || !is_string($password)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid registration details"
	]);
	exit;
}

$email = strtolower(trim($email));

if (($_SESSION["verified_registration_email"] ?? null) !== $email) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Please verify your email before registering"
    ]);
    exit;
}

if ($username === "" || $email === "" || $password === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "All fields are required"
    ]);
    exit;
}

if (!preg_match("/^(?!\s).{2,30}(?<!\s)$/u", $username)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Username must be 2 to 30 characters and cannot start or end with spaces"
    ]);
    exit;
}

if (!preg_match("/^(?!\s)(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\S$).{8,}$/u", $password)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Password must be at least 8 characters, include an uppercase letter, lowercase letter, and number, and cannot start or end with spaces"
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

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$roleId = 1;

$stmt = $conn->prepare(
    "INSERT INTO users (username, email, password, role_id)
     VALUES (?, ?, ?, ?)"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param(
    "sssi",
    $username,
    $email,
    $hashedPassword,
    $roleId
);

if (!$stmt->execute()) {
    if ($stmt->errno === 1062) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Username or email already exists"
        ]);
        exit;
    }
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->close();

unset(
    $_SESSION["verified_registration_email"],
);

echo json_encode([
    "success" => true,
    "message" => "Account created successfully"
]);