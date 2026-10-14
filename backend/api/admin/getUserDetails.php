<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireAdmin.php";

$targetId = $_GET["id"] ?? null;

if (!$targetId || !is_numeric($targetId)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid user ID"
	]);
	exit;
}

$stmt = $conn->prepare(
	"SELECT
		u.user_id,
		u.username,
		u.email,
		u.profile_picture,
		u.role_id,
		u.status,
		u.suspended_until,
		CASE
			WHEN u.status = 4 THEN 'banned'
			WHEN u.status = 1 AND u.suspended_until > NOW() THEN 'suspended'
			WHEN u.status = 1 THEN 'active'
		END AS user_status,
		(
			SELECT COUNT(*)
			FROM article a
			WHERE a.author_id = u.user_id
			AND a.status = 1
			AND a.published_at <= NOW()
		) AS article_count,
		(
			SELECT COUNT(*)
			FROM comment c
			WHERE c.user_id = u.user_id
			AND c.status = 1
		) AS comment_count,
		(
			SELECT COUNT(*)
			FROM report r
			LEFT JOIN article a ON r.article_id = a.article_id
			LEFT JOIN comment c ON r.comment_id = c.comment_id
			WHERE r.resolution_id IS NULL
			AND (a.author_id = u.user_id OR c.user_id = u.user_id)
		) AS pending_report_count,
		(
			SELECT COUNT(*)
			FROM user_moderation_history history
			WHERE history.user_id = u.user_id
			AND history.action = 'warning'
		) AS warning_count,
		(
			SELECT COUNT(*)
			FROM user_moderation_history history
			WHERE history.user_id = u.user_id
			AND history.action = 'suspension'
		) AS suspension_count
	 FROM users u
	 WHERE u.user_id = ?"
);

if (!$stmt) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->bind_param("i", $targetId);

if (!$stmt->execute()) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$result = $stmt->get_result();
$stmt->close();

$user = $result->fetch_assoc();

if (!$user) {
	http_response_code(404);
	echo json_encode([
		"success" => false,
		"message" => "User not found"
	]);
	exit;
}

$stmt = $conn->prepare(
	"SELECT
		history.history_id,
		history.action,
		history.reason,
		history.suspended_until,
		history.created_at,
		admin.username AS actor
	 FROM user_moderation_history history
	 JOIN users admin ON history.admin_id = admin.user_id
	 WHERE history.user_id = ?
	 ORDER BY history.created_at DESC"
);

if (!$stmt) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->bind_param("i", $targetId);

if (!$stmt->execute()) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$result = $stmt->get_result();
$stmt->close();

$history = [];

while ($row = $result->fetch_assoc()) {
	$history[] = $row;
}

echo json_encode([
	"success" => true,
	"user" => $user,
	"history" => $history
]);