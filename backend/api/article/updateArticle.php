<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../../config/sanitize.php";
require __DIR__ . "/../utils/requireUser.php";

$data = json_decode(file_get_contents("php://input"), true);
$articleId = $data["article_id"] ?? null;
$title = $data["title"] ?? "";
$content = $data["content"] ?? "";
$summary = $data["summary"] ?? "";
$thumbnail = $data["thumbnail"] ?? null;
$status = $data["status"] ?? null;
$categoryId = $data["category_id"] ?? null;
$publishedAt = $data["published_at"] ?? null;
$tagIds = $data["tag_ids"] ?? [];
$backgroundId = $data["background_cosmetic_id"] ?? null;
$userId = $_SESSION["user_id"];

if (!is_string($title) || !is_string($content) || !is_string($summary)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid article content"
	]);
	exit;
}

$title = trim($title);
$content = sanitizeHtml($content);
$summary = trim($summary);

if (($thumbnail !== null && !is_string($thumbnail)) || ($publishedAt !== null && !is_string($publishedAt))) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid thumbnail or publish date"
	]);
	exit;
}

if (!$articleId || !is_numeric($articleId)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid article ID"
    ]);
    exit;
}

if ($title === "" || $content === "" || $status === null) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Required fields are missing"
    ]);
    exit;
}

if (!$categoryId || !is_numeric($categoryId)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid category ID"
    ]);
    exit;
}

if ($status != 0 && $status != 1) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid article status"
    ]);
    exit;
}

if (!is_array($tagIds)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid tags",
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT author_id, status
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

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Article not found"]);
    exit;
}

$article = $result->fetch_assoc();

if ($article["status"] != 0 && $article["status"] != 1) {
    http_response_code(409);
    echo json_encode([
        "success" => false,
        "message" => "This article cannot be edited"
    ]);
    exit;
}

if ($article["author_id"] != $userId) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "You cannot edit this article"
    ]);
    exit;
}

if ($article["status"] == 1 && $status == 0) {
    http_response_code(409);
    echo json_encode([
        "success" => false,
        "message" => "Published articles cannot be reverted to drafts"
    ]);
    exit;
}

if ($backgroundId !== null && !is_numeric($backgroundId)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid background ID"
    ]);
    exit;
}

if ($backgroundId !== null) {
    $stmt = $conn->prepare(
        "SELECT c.cosmetic_id
         FROM cosmetic c
         JOIN user_cosmetic owned ON owned.cosmetic_id = c.cosmetic_id
         WHERE owned.user_id = ? AND c.cosmetic_id = ? AND c.cosmetic_type_id = 2"
    );

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Server error"]);
        exit;
    }

    $stmt->bind_param("ii", $userId, $backgroundId);

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Server error"]);
        exit;
    }

    $result = $stmt->get_result();
    $stmt->close();
    $background = $result->fetch_assoc();

    if (!$background) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Invalid or unowned article background"
        ]);
        exit;
    }
}

if (!$conn->begin_transaction()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

if ($article["status"] == 0) {
    if ($status == 1) {
        if ($publishedAt) {
            $scheduledDateTime = new DateTime($publishedAt);
            $scheduledDateTime->setTimezone(new DateTimeZone("Asia/Singapore"));
            $publishedAt = $scheduledDateTime->format("Y-m-d H:i:s");

            $stmt = $conn->prepare(
                "UPDATE article
                 SET
                    title = ?,
                    content = ?,
                    summary = ?,
                    thumbnail = ?,
                    status = ?,
                    published_at = ?,
                    category_id = ?,
					background_cosmetic_id = ?
                 WHERE article_id = ?"
            );
        } else {
            $stmt = $conn->prepare(
                "UPDATE article
                 SET
                    title = ?,
                    content = ?,
                    summary = ?,
                    thumbnail = ?,
                    status = ?,
                    published_at = NOW(),
                    category_id = ?,
					background_cosmetic_id = ?
                 WHERE article_id = ?"
            );
        }
    } else {
        $stmt = $conn->prepare(
            "UPDATE article
             SET
                title = ?,
                content = ?,
                summary = ?,
                thumbnail = ?,
                status = ?,
                published_at = NULL,
                category_id = ?,
					background_cosmetic_id = ?
             WHERE article_id = ?"
        );
    }
} else {
    $stmt = $conn->prepare(
        "UPDATE article
         SET
            title = ?,
            content = ?,
            summary = ?,
            thumbnail = ?,
            status = ?,
            updated_at = CASE
                WHEN published_at <= NOW() THEN NOW()
                ELSE updated_at
            END,
            category_id = ?,
			background_cosmetic_id = ?
         WHERE article_id = ?"
    );
}

if (!$stmt) {
	$conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

if ($article["status"] == 0 && $status == 1 && $publishedAt) {
    $stmt->bind_param(
        "ssssisiii",
        $title,
        $content,
        $summary,
        $thumbnail,
        $status,
        $publishedAt,
        $categoryId,
		$backgroundId,
        $articleId
    );
} else {
    $stmt->bind_param(
        "ssssiiii",
        $title,
        $content,
        $summary,
        $thumbnail,
        $status,
        $categoryId,
		$backgroundId,
        $articleId
    );
}

if (!$stmt->execute()) {
	$conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->close();

$stmt = $conn->prepare(
    "DELETE FROM article_tag
     WHERE article_id = ?"
);

if (!$stmt) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("i", $articleId);

if (!$stmt->execute()) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->close();

if (!empty($tagIds)) {
    $stmt = $conn->prepare(
        "INSERT INTO article_tag (article_id, tag_id)
         VALUES (?, ?)"
    );

    if (!$stmt) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Server error"]);
        exit;
    }

    foreach ($tagIds as $tagId) {
        if (!is_numeric($tagId)) {
            $conn->rollback();
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Invalid tag ID",
            ]);
            exit;
        }

        $stmt->bind_param("ii", $articleId, $tagId);

        if (!$stmt->execute()) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Server error"]);
            exit;
        }
    }

    $stmt->close();
}

if (!$conn->commit()) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => "Article updated successfully"
]);