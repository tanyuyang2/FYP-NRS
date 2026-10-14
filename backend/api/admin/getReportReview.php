<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireAdmin.php";

$articleId = $_GET["article_id"] ?? null;
$commentId = $_GET["comment_id"] ?? null;
$isArticle = $articleId !== null;
$targetId = $isArticle ? $articleId : $commentId;

if (($articleId === null) === ($commentId === null)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Provide an article or comment ID"
	]);
	exit;
}

if (!$targetId || !is_numeric($targetId)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid article or comment ID"
	]);
	exit;
}

if ($isArticle) {
	$stmt = $conn->prepare(
		"SELECT
			a.article_id,
			a.title AS preview,
			a.author_id,
			u.username AS author,
			r.reason
		 FROM report r
		 JOIN article a ON a.article_id = r.article_id
		 JOIN users u ON u.user_id = a.author_id
		 WHERE r.article_id = ? AND r.resolution_id IS NULL"
	);
} else {
	$stmt = $conn->prepare(
		"SELECT
			c.comment_id,
			c.article_id AS parent_article_id,
			c.content AS preview,
			c.user_id AS author_id,
			u.username AS author,
			r.reason
		 FROM report r
		 JOIN comment c ON c.comment_id = r.comment_id
		 JOIN users u ON u.user_id = c.user_id
		 WHERE r.comment_id = ? AND r.resolution_id IS NULL"
	);
}

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

if ($result->num_rows === 0) {
	http_response_code(404);
	echo json_encode([
		"success" => false,
		"message" => "This report is no longer pending or could not be found"
	]);
	exit;
}

$row = $result->fetch_assoc();

$report = [
	"article_id" => $isArticle ? $row["article_id"] : null,
	"comment_id" => $isArticle ? null : $row["comment_id"],
	"parent_article_id" => $isArticle ? null : $row["parent_article_id"],
	"preview" => $row["preview"],
	"author_id" => $row["author_id"],
	"author" => $row["author"],
	"reporters" => [
		["reason" => $row["reason"]]
	]
];

while ($row = $result->fetch_assoc()) {
	$report["reporters"][] = [
		"reason" => $row["reason"]
	];
}

$stmt = $conn->prepare(
	"SELECT
		COUNT(CASE WHEN action = 'warning' THEN 1 END) AS warning_count,
		COUNT(CASE WHEN action = 'suspension' THEN 1 END) AS suspension_count
	 FROM user_moderation_history
	 WHERE user_id = ?"
);

if (!$stmt) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$authorId = $report["author_id"];
$stmt->bind_param("i", $authorId);

if (!$stmt->execute()) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$result = $stmt->get_result();
$stmt->close();
$row = $result->fetch_assoc();

$report["warning_count"] = $row["warning_count"];
$report["suspension_count"] = $row["suspension_count"];

echo json_encode([
	"success" => true,
	"report" => $report
]);