<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireUser.php";

$userId = $_SESSION["user_id"];
$articleId = $_GET["id"] ?? null;

if (!$articleId || !is_numeric($articleId)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid article ID"
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT author_id
     FROM article
     WHERE article_id = ?"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("i", $articleId);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Article not found"
    ]);
    exit;
}

$article = $result->fetch_assoc();

if ($article["author_id"] != $userId) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "You cannot edit this article"
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT
        article_id,
        title,
        content,
        summary,
        thumbnail,
        category_id,
		background_cosmetic_id
     FROM article
     WHERE article_id = ?"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("i", $articleId);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$result = $stmt->get_result();
$stmt->close();
$article = $result->fetch_assoc();

$stmt = $conn->prepare(
    "SELECT t.tag_id, t.name
     FROM article_tag at
     JOIN tag t ON at.tag_id = t.tag_id
     WHERE at.article_id = ?
     ORDER BY t.name ASC"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("i", $articleId);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$result = $stmt->get_result();
$stmt->close();

$article["tags"] = [];

while ($tag = $result->fetch_assoc()) {
    $article["tags"][] = $tag;
}

echo json_encode([
    "success" => true,
    "article" => $article
]);