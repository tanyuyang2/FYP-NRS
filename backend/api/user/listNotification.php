<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireUser.php";

$userId = $_SESSION["user_id"];

$stmt = $conn->prepare(
	"SELECT
		n.notification_id,
		n.actor_id,
		n.article_id,
		n.type,
		n.message,
		n.is_read,
		n.created_at,
		u.username AS actor_username
	 FROM notification n
	 LEFT JOIN users u ON u.user_id = n.actor_id
	 WHERE n.user_id = ? AND n.created_at <= NOW()
	 ORDER BY n.created_at DESC"
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
$notifications = [];

while ($row = $result->fetch_assoc()) {
	$notifications[] = $row;
}

echo json_encode([
	"success" => true,
	"notifications" => $notifications
]);