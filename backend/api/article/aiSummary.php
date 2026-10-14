<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../../config/openai.php";

$data = json_decode(file_get_contents("php://input"), true);

$articleId = $data["article_id"] ?? null;

if (!$articleId || !is_numeric($articleId)) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid article ID"
    ]);

    exit;
}

$stmt = $conn->prepare(
	"SELECT summary, comment_count, generated_at
	 FROM article_ai_summary
	 WHERE article_id = ?"
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
$existing = $result->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare(
	"SELECT content, updated_at
	 FROM article
	 WHERE article_id = ?"
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
        "message" => "Article not found"
    ]);
    exit;
}

$stmt = $conn->prepare(
	"SELECT
		c.comment_id,
		c.content,
		c.parent_comment_id,
		u.username AS author
	 FROM comment c
	 JOIN users u ON c.user_id = u.user_id
	 WHERE c.article_id = ? AND c.status = 1"
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
$comments = [];
$commentCount = 0;

while ($comment = $result->fetch_assoc()) {
    $comments[] = $comment;
	$commentCount++;
}

if ($existing) {
	$summaryTime = strtotime($existing["generated_at"]);
	$articleTime = $article["updated_at"] ? strtotime($article["updated_at"]) : 0;

	if ($articleTime <= $summaryTime && abs($commentCount - $existing["comment_count"]) < 5) {
		echo json_encode([
			"success" => true,
			"summary" => $existing["summary"]
		]);
		exit;
	}
}

$repliesByParent = [];

foreach ($comments as $comment) {
	$key = $comment["parent_comment_id"] ?? "root";
	$repliesByParent[$key][] = $comment;
}

function buildCommentText($repliesByParent, $parent = null)
{
	$key = $parent["comment_id"] ?? "root";
	$text = "";

	foreach ($repliesByParent[$key] ?? [] as $comment) {
		if ($parent === null) {
			$text .= "\nComment #" . $comment["comment_id"];
			$text .= " by " . $comment["author"] . ":\n";
		} else {
			$text .= "\nReply #" . $comment["comment_id"];
			$text .= " by " . $comment["author"];
			$text .= " to comment #" . $parent["comment_id"];
			$text .= " by " . $parent["author"] . ":\n";
		}

		$text .= $comment["content"] . "\n";
		$text .= buildCommentText($repliesByParent, $comment);
	}

	return $text;
}

$commentText = buildCommentText($repliesByParent);

$prompt = 
	"You are an AI assistant for a gaming news website.

	Summarize the provided article and reader comments.

	Article:
	" . $article["content"] . "

	Reader Comments:
	" . $commentText . "

	Provide exactly two sections using these exact headings:

	Article Summary:
	Write one short paragraph summarizing the main points of the article.

	Comment Summary:
	Write one short paragraph summarizing the overall opinions and reactions.
	Use the author names and reply relationships to understand the discussion,
	but do not mention usernames in the summary.
	Do not treat multiple comments from the same author as multiple people's opinions.

	Formatting rules: - Use plain text headings exactly as written above. 
	- Do not use Markdown. - Do not add #, ##, ###, **, or other Markdown formatting. 
	- Do not add any other headings. 
	- Do not invent information that is not present in the provided text.";

$ch = curl_init("https://api.openai.com/v1/responses");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $openaiApiKey
]);

curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "model" => "gpt-5.6-luna",
	"reasoning" => [
        "effort" => "none"
    ],
    "input" => $prompt
]));

$response = curl_exec($ch);

if ($response === false) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "OpenAI request failed"
    ]);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($httpCode !== 200) {
    http_response_code(502);

    echo json_encode([
        "success" => false,
        "message" => "AI service unavailable, please try again later"
    ]);

    exit;
}

$data = json_decode($response, true);

if (!$data) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Invalid OpenAI response"
    ]);
    exit;
}

if (isset($data["error"])) {
    http_response_code(502);
    echo json_encode([
        "success" => false,
        "message" => "AI service returned an error"
    ]);
    exit;
}

$summary = null;

foreach ($data["output"] ?? [] as $output) {
	if (($output["type"] ?? "") === "message") {
		foreach ($output["content"] ?? [] as $content) {
			if (($content["type"] ?? "") === "output_text") {
				$summary = $content["text"] ?? null;
				break 2;
			}
		}
	}
}

if ($summary === null) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "OpenAI did not return a summary"
    ]);
    exit;
}

if ($existing) {
	$stmt = $conn->prepare(
		"UPDATE article_ai_summary
		 SET summary = ?, comment_count = ?, generated_at = NOW()
		 WHERE article_id = ?"
	);
} else {
	$stmt = $conn->prepare(
		"INSERT INTO article_ai_summary (
			article_id,
			summary,
			comment_count
		)
		 VALUES (?, ?, ?)"
	);
}

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

if ($existing) {
	$stmt->bind_param(
		"sii",
		$summary,
		$commentCount,
		$articleId
	);
} else {
	$stmt->bind_param(
		"isi",
		$articleId,
		$summary,
		$commentCount
	);
}

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->close();

echo json_encode([
    "success" => true,
    "summary" => $summary
]);