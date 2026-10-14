<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireAdmin.php";

$page = $_GET["page"] ?? "1";
$search = $_GET["search"] ?? "";
$status = $_GET["status"] ?? "all";
$limit = 10;

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

if (!in_array($status, ["all", "active", "suspended", "banned"], true)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid status"
	]);
	exit;
}

$page = (int) $page;
$offset = ($page - 1) * $limit;
$searchTerm = "%" . $search . "%";

$stmt = $conn->prepare(
	"SELECT COUNT(*)
	 FROM users
	 WHERE (username LIKE ? OR email LIKE ?)
	 AND (
		? = 'all'
		OR CASE
			WHEN status = 4 THEN 'banned'
			WHEN status = 1 AND suspended_until > NOW() THEN 'suspended'
			WHEN status = 1 THEN 'active'
		END = ?
	 )"
);

if (!$stmt) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->bind_param(
	"ssss",
	$searchTerm,
	$searchTerm,
	$status,
	$status
);

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
		user_id,
		username,
		email,
		role_id,
		status,
		suspended_until,
		CASE
			WHEN status = 4 THEN 'banned'
			WHEN status = 1 AND suspended_until > NOW() THEN 'suspended'
			WHEN status = 1 THEN 'active'
		END AS user_status
	 FROM users
	 WHERE (username LIKE ? OR email LIKE ?)
	 AND (
		? = 'all'
		OR CASE
			WHEN status = 4 THEN 'banned'
			WHEN status = 1 AND suspended_until > NOW() THEN 'suspended'
			WHEN status = 1 THEN 'active'
		END = ?
	 )
	 ORDER BY user_id ASC
	 LIMIT ? OFFSET ?"
);

if (!$stmt) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->bind_param(
	"ssssii",
	$searchTerm,
	$searchTerm,
	$status,
	$status,
	$limit,
	$offset
);

if (!$stmt->execute()) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$result = $stmt->get_result();
$stmt->close();

$users = [];

while ($row = $result->fetch_assoc()) {
	$users[] = $row;
}

echo json_encode([
	"success" => true,
	"users" => $users,
	"pagination" => [
		"page" => $page,
		"limit" => $limit,
		"total" => $total,
		"total_pages" => $totalPages
	]
]);
