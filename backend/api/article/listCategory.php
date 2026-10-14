<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";

$stmt = $conn->prepare(
    "SELECT category_id, category_name
     FROM category
     ORDER BY category_id ASC"
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

$categories = [];

while ($category = $result->fetch_assoc()) {
    $categories[] = $category;
}

echo json_encode([
    "success" => true,
    "categories" => $categories
]);