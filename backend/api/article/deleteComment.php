<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireUser.php";

$data = json_decode(file_get_contents("php://input"), true);
$commentId = $data["comment_id"] ?? null;
$userId = $_SESSION["user_id"];

if (!$commentId || !is_numeric($commentId)) {
    http_response_code(400);
    echo json_encode([
		"success" => false, 
		"message" => "Invalid comment ID"
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
        "message" => "You cannot delete this comment"
    ]);
    exit;
}

$stmt = $conn->prepare(
    "UPDATE comment
     SET status = 0
     WHERE comment_id = ? AND user_id = ?"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("ii", $commentId, $userId);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->close();

echo json_encode([
    "success" => true,
    "message" => "Comment deleted"
]);