<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireUser.php";

$data = json_decode(file_get_contents("php://input"), true);
$email = $data["email"] ?? "";
$otp = $data["otp"] ?? "";
$userId = $_SESSION["user_id"];

if (!is_string($email) || !is_string($otp)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid email or verification code"
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

if (!preg_match("/^[0-9]{6}$/", $otp)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Enter a 6-digit verification code"
    ]);
    exit;
}

if (!$conn->begin_transaction()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT
        verification_id,
        otp_hash,
        attempts,
        expire_at <= NOW() AS is_expired
     FROM email_verification
     WHERE email = ? AND type = 2 AND user_id = ?
     FOR UPDATE"
);

if (!$stmt) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("si", $email, $userId);

if (!$stmt->execute()) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$result = $stmt->get_result();
$stmt->close();
$verification = $result->fetch_assoc();

if (!$verification || $verification["is_expired"] == 1) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Verification code expired or not found. Request a new code."
    ]);
    exit;
}

if ($verification["attempts"] >= 5) {
    $conn->rollback();
    http_response_code(429);
    echo json_encode([
        "success" => false,
        "message" => "Too many incorrect attempts. Request a new code."
    ]);
    exit;
}

$verificationId = $verification["verification_id"];

if (!password_verify($otp, $verification["otp_hash"])) {
    $stmt = $conn->prepare(
        "UPDATE email_verification
         SET attempts = attempts + 1
         WHERE verification_id = ?"
    );

    if (!$stmt) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Server error"]);
        exit;
    }

    $stmt->bind_param("i", $verificationId);

    if (!$stmt->execute()) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Server error"]);
        exit;
    }

    $stmt->close();

    if (!$conn->commit()) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Server error"]);
        exit;
    }

    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Incorrect verification code"
    ]);
    exit;
}

$stmt = $conn->prepare(
    "DELETE FROM email_verification
     WHERE verification_id = ?"
);

if (!$stmt) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("i", $verificationId);

if (!$stmt->execute()) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->close();

if (!$conn->commit()) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$_SESSION["verified_change_email"] = [
    "user_id" => $userId,
    "email" => $email
];

echo json_encode([
    "success" => true,
    "message" => "Email verified successfully"
]);