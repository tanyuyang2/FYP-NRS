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
	"SELECT COUNT(*)
	 FROM report_resolution"
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
		rr.resolution_id,
		rr.resolved_at,
		u.username AS actor,
		rr.action,
		targets.article_id,
		targets.comment_id
	 FROM report_resolution rr
	 JOIN users u ON rr.admin_id = u.user_id
	 LEFT JOIN (
		SELECT DISTINCT resolution_id, article_id, comment_id
		FROM report
		WHERE resolution_id IS NOT NULL
	 ) targets ON rr.resolution_id = targets.resolution_id
	 ORDER BY rr.resolved_at DESC
	 LIMIT ? OFFSET ?"
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