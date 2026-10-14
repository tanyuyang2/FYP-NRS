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
    "SELECT article_id
     FROM article_bookmark
     WHERE article_id = ? AND user_id = ?"
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

$result = $stmt->get_result();
$stmt->close();
$existingBookmark = $result->fetch_assoc();

if ($existingBookmark) {
    $stmt = $conn->prepare(
        "DELETE FROM article_bookmark
         WHERE article_id = ? AND user_id = ?"
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
    $bookmarked = false;
} else {
    $stmt = $conn->prepare(
        "INSERT INTO article_bookmark (article_id, user_id)
         VALUES (?, ?)"
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
    $bookmarked = true;
}

echo json_encode([
    "success" => true,
    "bookmarked" => $bookmarked
]);