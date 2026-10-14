<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/recordArticleView.php";
require __DIR__ . "/../utils/requireAdmin.php";

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
	"SELECT
		a.article_id,
		a.title,
		a.content,
		a.thumbnail,
		a.author_id,
		a.published_at,
		a.updated_at,
		a.status,
		u.username AS author,
		u.profile_picture AS author_profile_picture,
		(
			SELECT cosmetic.image_url
			FROM cosmetic cosmetic
			JOIN user_equipped_cosmetic equipped ON cosmetic.cosmetic_id = equipped.cosmetic_id AND cosmetic.cosmetic_type_id = equipped.cosmetic_type_id
			WHERE equipped.user_id = a.author_id AND cosmetic.cosmetic_type_id = 1
		) AS author_profile_frame_url,
		c.category_name AS category,
		bg.image_url AS background_url
	 FROM article a
	 JOIN users u ON a.author_id = u.user_id
	 JOIN category c ON a.category_id = c.category_id
	 LEFT JOIN cosmetic bg ON bg.cosmetic_id = a.background_cosmetic_id AND bg.cosmetic_type_id = 2
	 WHERE a.article_id = ?
	 AND a.status IN (1, 3, 4) AND a.published_at <= NOW()"
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

if (!$article) {
	http_response_code(404);
	echo json_encode([
		"success" => false,
		"message" => "Article not found or unavailable"
	]);
	exit;
}

recordArticleView($conn, $articleId);

echo json_encode([
	"success" => true,
	"article" => $article
]);