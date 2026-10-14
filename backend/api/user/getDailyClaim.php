<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireUser.php";

$userId = $_SESSION["user_id"];
$today = new DateTime();
$today->setTimezone(new DateTimeZone("Asia/Singapore"));
$referenceKey = "daily_login:" . $today->format("Y-m-d");

$stmt = $conn->prepare(
    "SELECT transaction_id
     FROM transaction
     WHERE user_id = ? AND reference_key = ?"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("is", $userId, $referenceKey);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$result = $stmt->get_result();
$claimed = $result->num_rows > 0;
$stmt->close();

echo json_encode([
    "success" => true,
    "claimed" => $claimed
]);