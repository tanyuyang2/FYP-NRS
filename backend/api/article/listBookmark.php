<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireUser.php";

$userId = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT
        a.article_id,
        a.title,
        a.summary,
        a.thumbnail,
        a.published_at,
        a.updated_at,
        u.username AS author,
        c.category_name AS category
     FROM article a
     JOIN article_bookmark ab ON a.article_id = ab.article_id
     JOIN users u ON a.author_id = u.user_id
     JOIN category c ON a.category_id = c.category_id
     WHERE ab.user_id = ? AND a.status = 1 AND a.published_at <= NOW()
     ORDER BY ab.created_at DESC"
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
$articles = [];

while ($row = $result->fetch_assoc()) {
    $articles[] = $row;
}

echo json_encode([
    "success" => true,
    "articles" => $articles
]);