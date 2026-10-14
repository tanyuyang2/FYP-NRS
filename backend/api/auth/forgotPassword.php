<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../../config/resend.php";
require __DIR__ . "/../../vendor/autoload.php";

$data = json_decode(file_get_contents("php://input"), true);
$email = $data["email"] ?? "";

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

$message = "If an account exists for this email, a reset link has been sent.";

$stmt = $conn->prepare(
    "SELECT user_id
     FROM users
     WHERE email = ?"
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

if (!$user) {
    echo json_encode([
        "success" => true,
        "message" => $message
    ]);
    exit;
}

$userId = $user["user_id"];

$stmt = $conn->prepare(
    "SELECT created_at > NOW() - INTERVAL 5 MINUTE AS on_cooldown
     FROM account_token
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
$tokenRecord = $result->fetch_assoc();

if ($tokenRecord && $tokenRecord["on_cooldown"] == 1) {
    echo json_encode([
        "success" => true,
        "message" => $message
    ]);
    exit;
}

$token = bin2hex(random_bytes(32));
$tokenHash = hash("sha256", $token);

$stmt = $conn->prepare(
    "INSERT INTO account_token (
        user_id,
        token_hash,
        expire_at
    )
     VALUES (?, ?, NOW() + INTERVAL 1 HOUR)
     ON DUPLICATE KEY UPDATE
        token_hash = VALUES(token_hash),
        expire_at = NOW() + INTERVAL 1 HOUR,
        created_at = NOW()"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("is", $userId, $tokenHash);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->close();

// From config, not the Host header: a forged Host would put an attacker's
// domain in the emailed link and leak the token.
$appUrl = rtrim(nrs_env("APP_URL", "http://localhost:5173"), "/");
$resetUrl = $appUrl . "/reset-password?token=" . $token;

try {
    $resend = Resend::client($resendApiKey);

    $resend->emails->send([
        "from" => "NRS <nrs@info.rabutenri.com>",
        "to" => [$email],
        "subject" => "Reset your NRS password",
        "html" => "
            <h2>Reset your password</h2>
            <p>Click the link below to reset your password.</p>
            <p>
                <a href=\"$resetUrl\">Reset password</a>
            </p>
            <p>This link expires in 1 hour.</p>
            <p>If you did not request a password reset, you can ignore this email.</p>
        "
    ]);
} catch (Throwable $e) {
    error_log("Failed to send password reset email for user ID: " . $userId);
}

echo json_encode([
    "success" => true,
    "message" => $message
]);