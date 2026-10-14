<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireUser.php";

$data = json_decode(file_get_contents("php://input"), true);
$commentId = $data["comment_id"] ?? null;
$reaction = $data["reaction"] ?? null;
$userId = $_SESSION["user_id"];

if (!$commentId || !is_numeric($commentId)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid comment ID"
    ]);
    exit;
}

if ($reaction !== null && $reaction !== 0 && $reaction !== 1) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid reaction value"
    ]);
    exit;
}

if ($reaction === null) {
    $stmt = $conn->prepare(
        "DELETE FROM comment_reaction
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
} else {
    $stmt = $conn->prepare(
        "INSERT INTO comment_reaction (comment_id, user_id, reaction)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE reaction = VALUES(reaction)"
    );

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Server error"]);
        exit;
    }

    $stmt->bind_param("iii", $commentId, $userId, $reaction);

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Server error"]);
        exit;
    }

    $stmt->close();
}

$stmt = $conn->prepare(
    "SELECT
        SUM(reaction = 1) AS like_count,
        SUM(reaction = 0) AS dislike_count
     FROM comment_reaction
     WHERE comment_id = ?"
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
$counts = $result->fetch_assoc();

echo json_encode([
    "success" => true,
    "user_reaction" => $reaction,
    "like_count" => (int) ($counts["like_count"] ?? 0),
    "dislike_count" => (int) ($counts["dislike_count"] ?? 0)
]);