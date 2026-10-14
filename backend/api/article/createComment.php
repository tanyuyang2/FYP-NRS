<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireUser.php";

$data = json_decode(file_get_contents("php://input"), true);
$articleId = $data["article_id"] ?? null;
$parentCommentId = $data["parent_comment_id"] ?? null;
$content = $data["content"] ?? "";
$userId = $_SESSION["user_id"];

if (!is_string($content)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid comment content"
	]);
	exit;
}

$content = trim($content);

if (!$articleId || !is_numeric($articleId)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid article ID"
    ]);
    exit;
}

if ($content === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Comment cannot be empty"
    ]);
    exit;
}

if (mb_strlen($content) > 2000) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Comment cannot exceed 2000 characters"
    ]);
    exit;
}

if ($parentCommentId === null) {
   $stmt = $conn->prepare(
        "INSERT INTO comment (article_id, user_id, content)
         SELECT article_id, ?, ?
         FROM article
         WHERE article_id = ?
         AND status = 1
         AND published_at <= NOW()"
    );
} else {
    if (!is_numeric($parentCommentId)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Invalid parent comment ID"
        ]);
        exit;
    }

	$stmt = $conn->prepare(
        "INSERT INTO comment (
            article_id, 
			user_id, 
			content, 
			parent_comment_id
        )
         SELECT a.article_id, ?, ?, c.comment_id
         FROM article a
         JOIN comment c ON a.article_id = c.article_id
         WHERE a.article_id = ?
         AND a.status = 1
         AND a.published_at <= NOW()
         AND c.comment_id = ?
         AND c.status = 1"
    );
}

if (!$stmt) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

if ($parentCommentId === null) {
    $stmt->bind_param("isi", $userId, $content, $articleId);
} else {
    $stmt->bind_param("isii", $userId, $content, $articleId, $parentCommentId);
}

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

if ($stmt->affected_rows === 0) {
    $stmt->close();
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Article or parent comment not available"
    ]);
    exit;
}

$commentId = $stmt->insert_id;
$stmt->close();

$stmt = $conn->prepare(
    "SELECT
        c.comment_id,
        c.article_id,
        c.user_id,
        c.content,
        c.parent_comment_id,
        c.created_at,
        c.updated_at,
        u.username,
        u.profile_picture,
		(
			SELECT cosmetic.image_url
			FROM cosmetic
			JOIN user_equipped_cosmetic equipped ON cosmetic.cosmetic_id = equipped.cosmetic_id
			WHERE equipped.user_id = u.user_id AND cosmetic.cosmetic_type_id = 1
		) AS profile_frame_url
     FROM comment c
     JOIN users u ON c.user_id = u.user_id
     WHERE c.comment_id = ?"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("i", $commentId);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$result = $stmt->get_result();
$stmt->close();

$comment = $result->fetch_assoc();

echo json_encode([
    "success" => true,
    "comment" => $comment
]);