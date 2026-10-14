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
        c.cosmetic_type_id,
        c.price,
        CASE
            WHEN owned.cosmetic_id IS NULL THEN 0
            ELSE 1
        END AS is_owned
     FROM cosmetic c
     LEFT JOIN user_cosmetic owned ON owned.cosmetic_id = c.cosmetic_id AND owned.user_id = ?
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