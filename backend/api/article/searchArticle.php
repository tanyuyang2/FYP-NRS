<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";

$page = $_GET["page"] ?? "1";
$limit = 5;
$search = $_GET["search"] ?? "";
$tagId = $_GET["tag_id"] ?? null;

if (!is_string($search)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid search"
	]);
	exit;
}

$search = trim($search);

if (!ctype_digit($page) || (int) $page < 1) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid page"
	]);
	exit;
}

if ($tagId !== null && (!ctype_digit($tagId) || $tagId < 1)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid tag ID"
	]);
	exit;
}

$tagName = null;

if ($tagId !== null) {
	$stmt = $conn->prepare(
		"SELECT name
		 FROM tag
		 WHERE tag_id = ?"
	);

	if (!$stmt) {
		http_response_code(500);
		echo json_encode(["success" => false, "message" => "Server error"]);
		exit;
	}

	$stmt->bind_param("i", $tagId);

	if (!$stmt->execute()) {
		http_response_code(500);
		echo json_encode(["success" => false, "message" => "Server error"]);
		exit;
	}

	$result = $stmt->get_result();
	$stmt->close();
	$tag = $result->fetch_assoc();

	if (!$tag) {
		http_response_code(404);
		echo json_encode([
			"success" => false,
			"message" => "Tag not found"
		]);
		exit;
	}

	$tagName = $tag["name"];
}

$page = (int) $page;
$offset = ($page - 1) * $limit;

$searchTerm = "%" . $search . "%";

if ($tagId !== null) {
	$stmt = $conn->prepare(
		"SELECT COUNT(*)
		 FROM article a
		 JOIN article_tag at ON a.article_id = at.article_id
		 WHERE a.status = 1
		 AND a.published_at <= NOW()
		 AND at.tag_id = ?"
	);
} else {
	$stmt = $conn->prepare(
		"SELECT COUNT(*)
		 FROM article
		 WHERE status = 1
		 AND published_at <= NOW()
		 AND (
			title LIKE ?
			OR summary LIKE ?
		 )"
	);
}

if (!$stmt) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

if ($tagId !== null) {
	$stmt->bind_param("i", $tagId);
} else {
	$stmt->bind_param("ss", $searchTerm, $searchTerm);
}

if (!$stmt->execute()) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

$totalPages = (int) ceil($total / $limit);

if ($tagId !== null) {
	$stmt = $conn->prepare(
		"SELECT
			a.article_id,
			a.title,
			a.summary,
			a.thumbnail,
			a.published_at,
			a.updated_at,
			u.username AS author,
			c.category_name AS category
		 FROM article a
		 JOIN users u ON a.author_id = u.user_id
		 JOIN category c ON a.category_id = c.category_id
		 JOIN article_tag at ON a.article_id = at.article_id
		 WHERE a.status = 1
		 AND a.published_at <= NOW()
		 AND at.tag_id = ?
		 ORDER BY a.published_at DESC
		 LIMIT ? OFFSET ?"
	);
} else {
	$stmt = $conn->prepare(
		"SELECT
			a.article_id,
			a.title,
			a.summary,
			a.thumbnail,
			a.published_at,
			a.updated_at,
			u.username AS author,
			c.category_name AS category
		 FROM article a
		 JOIN users u ON a.author_id = u.user_id
		 JOIN category c ON a.category_id = c.category_id
		 WHERE a.status = 1
		 AND a.published_at <= NOW()
		 AND (
			a.title LIKE ?
			OR a.summary LIKE ?
		)
		 ORDER BY a.published_at DESC
		 LIMIT ? OFFSET ?"
	);
}

if (!$stmt) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

if ($tagId !== null) {
	$stmt->bind_param(
		"iii", 
		$tagId, 
		$limit, 
		$offset
	);
} else {
	$stmt->bind_param(
		"ssii",
		$searchTerm,
		$searchTerm,
		$limit,
		$offset
	);
}

if (!$stmt->execute()) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$result = $stmt->get_result();
$stmt->close();
$articles = [];

while ($article = $result->fetch_assoc()) {
	$articles[] = $article;
}

echo json_encode([
	"success" => true,
	"articles" => $articles,
	"tag_name" => $tagName,
	"pagination" => [
		"page" => $page,
		"limit" => $limit,
		"total" => $total,
		"total_pages" => $totalPages
	]
]);