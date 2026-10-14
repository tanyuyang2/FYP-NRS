<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireUser.php";

$userId = $_SESSION["user_id"];
$amount = 100;
$today = new DateTime();
$today->setTimezone(new DateTimeZone("Asia/Singapore"));
$referenceKey = "daily_login:" . $today->format("Y-m-d");

if (!$conn->begin_transaction()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO transaction (
	 user_id, 
	 amount, 
	 reason, 
	 reference_key
	)
     VALUES (?, ?, 'daily_login', ?)"
);

if (!$stmt) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("iis", $userId, $amount, $referenceKey);

if (!$stmt->execute()) {
    $conn->rollback();
    if ($stmt->errno === 1062) {
        echo json_encode([
            "success" => true,
            "message" => "Daily coins already claimed"
        ]);
        exit;
    }
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->close();

$stmt = $conn->prepare(
    "UPDATE users
     SET coin = coin + ?
     WHERE user_id = ?"
);

if (!$stmt) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("ii", $amount, $userId);

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

echo json_encode([
    "success" => true,
    "amount" => $amount,
    "message" => "You received $amount daily coins"
]);