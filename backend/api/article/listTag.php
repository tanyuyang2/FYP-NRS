<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";

$stmt = $conn->prepare(
    "SELECT tag_id, name
     FROM tag
     ORDER BY name ASC"
);

if (!$stmt) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

if (!$stmt->execute()) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$result = $stmt->get_result();
$stmt->close();
$tags = [];

while ($tag = $result->fetch_assoc()) {
    $tags[] = $tag;
}

echo json_encode([
    "success" => true,
    "tags" => $tags,
]);