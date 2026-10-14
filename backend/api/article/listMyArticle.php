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
        a.status,
		(a.status = 1 AND a.published_at > NOW()) AS is_scheduled,
		u.username AS author,
        c.category_name AS category
     FROM article a
     JOIN category c ON a.category_id = c.category_id
     JOIN users u ON a.author_id = u.user_id
     WHERE a.author_id = ?
     ORDER BY a.published_at DESC, a.article_id DESC"
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
$published = [];
$drafts = [];

while ($article = $result->fetch_assoc()) {
	if ($article["status"] == 1) {
		$published[] = $article;
	} elseif ($article["status"] == 0) {
		$drafts[] = $article;
	}
}

echo json_encode([
    "success" => true,
    "published" => $published,
    "drafts" => $drafts
]);