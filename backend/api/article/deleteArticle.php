<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireUser.php";

$data = json_decode(file_get_contents("php://input"), true);
$articleId = $data["article_id"] ?? null;
$userId = $_SESSION["user_id"];

if (!$articleId || !is_numeric($articleId)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid article ID"
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT author_id FROM article WHERE article_id = ?"
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
        "message" => "You cannot delete this article"
    ]);
    exit;
}

$stmt = $conn->prepare(
    "UPDATE article
     SET status = 3
     WHERE article_id = ? AND author_id = ?"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("ii", $articleId, $userId);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->close();

echo json_encode([
    "success" => true,
    "message" => "Article deleted"
]);