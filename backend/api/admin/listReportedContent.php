<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireAdmin.php";

$page = $_GET["page"] ?? "1";
$limit = 10;

if (!ctype_digit($page) || (int) $page < 1) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid page"
	]);
	exit;
}

$page = (int) $page;
$offset = ($page - 1) * $limit;

$stmt = $conn->prepare(
	"SELECT COUNT(DISTINCT article_id) + COUNT(DISTINCT comment_id)
	 FROM report
	 WHERE resolution_id IS NULL"
);

if (!$stmt) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
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

$stmt = $conn->prepare(
	"SELECT
		targets.article_id,
		targets.comment_id,
		targets.report_count,
		COALESCE(a.title, c.content) AS preview
	 FROM (
		SELECT
			article_id,
			comment_id,
			COUNT(*) AS report_count,
			MIN(created_at) AS first_reported_at
		FROM report
		WHERE resolution_id IS NULL
		GROUP BY article_id, comment_id
		ORDER BY report_count DESC, first_reported_at ASC
		LIMIT ? OFFSET ?
	 ) targets
	 LEFT JOIN article a ON targets.article_id = a.article_id
	 LEFT JOIN comment c ON targets.comment_id = c.comment_id
	 ORDER BY targets.report_count DESC, targets.first_reported_at ASC"
);

if (!$stmt) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->bind_param("ii", $limit, $offset);

if (!$stmt->execute()) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$result = $stmt->get_result();
$stmt->close();
$reports = [];

while ($row = $result->fetch_assoc()) {
	$reports[] = $row;
}

echo json_encode([
	"success" => true,
	"reports" => $reports,
	"pagination" => [
		"page" => $page,
		"limit" => $limit,
		"total" => $total,
		"total_pages" => $totalPages
	]
]);