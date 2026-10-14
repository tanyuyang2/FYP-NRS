<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../../config/resend.php";
require __DIR__ . "/../../vendor/autoload.php";
require __DIR__ . "/../utils/requireUser.php";

$data = json_decode(file_get_contents("php://input"), true);
$email = $data["email"] ?? "";
$userId = $_SESSION["user_id"];

if (!is_string($email)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid email"
	]);
	exit;
}

$email = strtolower(trim($email));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid email format"
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT user_id FROM users WHERE email = ?"
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

if ($result->num_rows > 0) {
    http_response_code(409);
    echo json_encode([
        "success" => false,
        "message" => "Email already exists"
    ]);
    exit;
}

$otp = str_pad((string) random_int(0, 999999), 6, "0", STR_PAD_LEFT);
$otpHash = password_hash($otp, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "SELECT created_at > NOW() - INTERVAL 60 SECOND AS on_cooldown
     FROM email_verification
     WHERE email = ? AND type = 2"
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
$verification = $result->fetch_assoc();

if ($verification && $verification["on_cooldown"] == 1) {
    http_response_code(429);
    echo json_encode([
        "success" => false,
        "message" => "Please wait 60 seconds before requesting another code"
    ]);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO email_verification (
        user_id,
        otp_hash,
        email,
        type,
        expire_at
     )
     VALUES (?, ?, ?, 2, NOW() + INTERVAL 5 MINUTE)
     ON DUPLICATE KEY UPDATE
        user_id = VALUES(user_id),
        otp_hash = VALUES(otp_hash),
        attempts = 0,
        expire_at = NOW() + INTERVAL 5 MINUTE,
        created_at = NOW()"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("iss", $userId, $otpHash, $email);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->close();

unset($_SESSION["verified_change_email"]);

try {
    $resend = Resend::client($resendApiKey);

    $resend->emails->send([
        "from" => "NRS <nrs@info.rabutenri.com>",
        "to" => [$email],
        "subject" => "NRS OTP",
        "html" => "
            <h2>Verify your email</h2>
            <p>Your verification code is:</p>
            <h1>$otp</h1>
            <p>This code expires in 5 minutes.</p>
            <p>If you did not request this code, you can ignore this email.</p>
        "
    ]);
} catch (Throwable $e) {
    http_response_code(502);
    echo json_encode([
        "success" => false,
        "message" => "Unable to send verification email. Please wait 60 seconds before requesting another code."
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => "Verification code sent. Check your email."
]);