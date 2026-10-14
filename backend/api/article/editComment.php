<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireUser.php";

$data = json_decode(file_get_contents("php://input"), true);
$commentId = $data["comment_id"] ?? null;
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

if (!$commentId || !is_numeric($commentId)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid comment ID"
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

$stmt = $conn->prepare(
    "SELECT user_id FROM comment WHERE comment_id = ?"
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

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Comment not found"
    ]);
    exit;
}

$comment = $result->fetch_assoc();

if ($comment["user_id"] != $userId) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "You cannot edit this comment"
    ]);
    exit;
}

$stmt = $conn->prepare(
    "UPDATE comment
     SET content = ?, updated_at = NOW()
     WHERE comment_id = ? AND user_id = ?"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("sii", $content, $commentId, $userId);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

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