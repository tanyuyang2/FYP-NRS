<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireAdmin.php";

$page = $_GET["page"] ?? "1";
$action = $_GET["action"] ?? "all";
$limit = 10;

if (!ctype_digit($page) || (int) $page < 1) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid page"
	]);
	exit;
}

if (!in_array($action, ["all", "warning", "suspension", "ban", "lift_suspension", "unban"], true)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid action"
	]);
	exit;
}

$page = (int) $page;
$offset = ($page - 1) * $limit;

$stmt = $conn->prepare(
	"SELECT COUNT(*)
	 FROM user_moderation_history
	 WHERE ? = 'all' OR action = ?"
);

if (!$stmt) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->bind_param("ss", $action, $action);

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
		history.history_id,
		history.created_at,
		admin.username AS actor,
		history.action,
		history.user_id,
		target.username AS target,
		history.reason
	 FROM user_moderation_history history
	 JOIN users admin ON history.admin_id = admin.user_id
	 JOIN users target ON history.user_id = target.user_id
	 WHERE ? = 'all' OR history.action = ?
	 ORDER BY history.created_at DESC
	 LIMIT ? OFFSET ?"
);

if (!$stmt) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->bind_param("ssii", $action, $action, $limit, $offset);

if (!$stmt->execute()) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$result = $stmt->get_result();
$stmt->close();
$records = [];

while ($row = $result->fetch_assoc()) {
	$records[] = $row;
}

echo json_encode([
	"success" => true,
	"records" => $records,
	"pagination" => [
		"page" => $page,
		"limit" => $limit,
		"total" => $total,
		"total_pages" => $totalPages
	]
]);