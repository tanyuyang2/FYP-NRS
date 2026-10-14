<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireUser.php";

$data = json_decode(file_get_contents("php://input"), true);
$notificationId = $data["notification_id"] ?? null;
$userId = $_SESSION["user_id"];

if ($notificationId === null) {
    $stmt = $conn->prepare(
        "UPDATE notification
         SET is_read = 1
         WHERE user_id = ? AND is_read = 0 AND created_at <= NOW()"
    );
} else {
    if (!is_numeric($notificationId)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Invalid notification ID"
        ]);
        exit;
    }

    $stmt = $conn->prepare(
        "UPDATE notification
         SET is_read = 1
         WHERE notification_id = ? AND user_id = ? AND created_at <= NOW()"
    );
}

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

if ($notificationId === null) {
    $stmt->bind_param("i", $userId);
} else {
    $stmt->bind_param("ii", $notificationId, $userId);
}

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->close();

echo json_encode(["success" => true]);