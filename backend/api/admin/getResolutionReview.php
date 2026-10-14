<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireAdmin.php";

$resolutionId = $_GET["resolution_id"] ?? null;

if (!$resolutionId || !is_numeric($resolutionId)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid resolution ID"
	]);
	exit;
}

$stmt = $conn->prepare(
	"SELECT
		rr.resolution_id,
		rr.notes,
		r.article_id,
		r.comment_id,
		c.article_id AS parent_article_id,
		COALESCE(a.title, c.content) AS preview,
		author.username AS author,
		r.reason
	 FROM report_resolution rr
	 JOIN report r ON rr.resolution_id = r.resolution_id
	 LEFT JOIN article a ON r.article_id = a.article_id
	 LEFT JOIN comment c ON r.comment_id = c.comment_id
	 LEFT JOIN users author ON author.user_id = COALESCE(a.author_id, c.user_id)
	 WHERE rr.resolution_id = ?"
);

if (!$stmt) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->bind_param("i", $resolutionId);

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
		"message" => "Resolution not found"
	]);
	exit;
}

$row = $result->fetch_assoc();

$resolution = [
	"resolution_id" => $row["resolution_id"],
	"notes" => $row["notes"],
	"article_id" => $row["article_id"],
	"comment_id" => $row["comment_id"],
	"parent_article_id" => $row["parent_article_id"],
	"preview" => $row["preview"],
	"author" => $row["author"],
	"reporters" => [
		["reason" => $row["reason"]]
	]
];

while ($row = $result->fetch_assoc()) {
	$resolution["reporters"][] = [
		"reason" => $row["reason"]
	];
}

echo json_encode([
	"success" => true,
	"resolution" => $resolution
]);