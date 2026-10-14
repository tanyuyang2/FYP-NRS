<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireUser.php";

$data = json_decode(file_get_contents("php://input"), true);
$articleId = $data["article_id"] ?? null;
$reaction = $data["reaction"] ?? null;
$userId = $_SESSION["user_id"];

if (!$articleId || !is_numeric($articleId)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid article ID"
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
        "DELETE FROM article_reaction
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
} else {
    $stmt = $conn->prepare(
        "INSERT INTO article_reaction (article_id, user_id, reaction)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE reaction = VALUES(reaction)"
    );

    if (!$stmt) {
		http_response_code(500);
		echo json_encode(["success" => false, "message" => "Server error"]);
		exit;
	}

    $stmt->bind_param("iii", $articleId, $userId, $reaction);

    if (!$stmt->execute()) {
		http_response_code(500);
		echo json_encode(["success" => false, "message" => "Server error"]);
		exit;
	}

    $stmt->close();
}

$stmt = $conn->prepare(
    "SELECT
        SUM(reaction = 1) AS helpful_count,
        SUM(reaction = 0) AS unhelpful_count
     FROM article_reaction
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
$counts = $result->fetch_assoc();

echo json_encode([
    "success" => true,
    "user_reaction" => $reaction,
	"helpful_count" => (int) ($counts["helpful_count"] ?? 0),
	"unhelpful_count" => (int) ($counts["unhelpful_count"] ?? 0)
]);