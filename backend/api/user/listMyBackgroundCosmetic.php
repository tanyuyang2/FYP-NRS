<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireUser.php";

$userId = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT
        c.cosmetic_id,
        c.name,
        c.image_url,
        c.rarity,
        c.cosmetic_type_id
     FROM cosmetic c
     JOIN user_cosmetic owned ON c.cosmetic_id = owned.cosmetic_id
     WHERE owned.user_id = ? AND c.cosmetic_type_id = 2
     ORDER BY c.cosmetic_id"
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
$cosmetics = [];

while ($cosmetic = $result->fetch_assoc()) {
    $cosmetics[] = $cosmetic;
}

echo json_encode([
    "success" => true,
    "cosmetics" => $cosmetics
]);