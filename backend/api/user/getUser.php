<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";

/*
 * Trust score: the author's article bridging scores (backend/cron/bridgingScore.php),
 * averaged with TRUST_PRIOR pseudo-articles at 0 so a few lucky articles cannot
 * carry it, then mapped so 50 is neutral. TRUST_SCALE fits the 2026-10 spread
 * of article scores (about -0.3 to +0.3); recalibrate it as ratings grow.
 */
const TRUST_MIN_ARTICLES = 3;
const TRUST_PRIOR = 3;
const TRUST_SCALE = 250;
// [Control] posts: one universally helpful, one universally unhelpful.
const TRUST_EXCLUDED_ARTICLES = "55, 56";

$userId = $_GET["id"] ?? null;

if (!$userId || !is_numeric($userId)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid user ID",
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT
        u.user_id,
        u.username,
        u.profile_picture,
		u.bio,
		(
            SELECT c.image_url
            FROM cosmetic c 
            JOIN user_equipped_cosmetic equipped ON c.cosmetic_id = equipped.cosmetic_id AND c.cosmetic_type_id = equipped.cosmetic_type_id
            WHERE equipped.user_id = u.user_id AND c.cosmetic_type_id = 1
        ) AS profile_frame_url,
        (SELECT COUNT(*)
         FROM user_subscription
         WHERE subscribed_to_id = u.user_id) AS followers_count,
        (SELECT COUNT(*)
         FROM user_subscription
         WHERE subscriber_id = u.user_id) AS following_count,
        (SELECT COUNT(*)
         FROM article a
         WHERE a.author_id = u.user_id AND a.status = 1 AND a.published_at <= NOW()
         AND a.bridging_score IS NOT NULL
         AND a.article_id NOT IN (" . TRUST_EXCLUDED_ARTICLES . ")) AS rated_articles,
        (SELECT COALESCE(SUM(a.bridging_score), 0)
         FROM article a
         WHERE a.author_id = u.user_id AND a.status = 1 AND a.published_at <= NOW()
         AND a.bridging_score IS NOT NULL
         AND a.article_id NOT IN (" . TRUST_EXCLUDED_ARTICLES . ")) AS bridging_sum
     FROM users u
     WHERE u.user_id = ?"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("i", $userId);

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
        "message" => "User not found",
    ]);
    exit;
}

$ratedArticles = (int) $user["rated_articles"];
$user["trust_score"] = $ratedArticles >= TRUST_MIN_ARTICLES
    ? (int) max(0, min(100, round(
        50 + TRUST_SCALE * $user["bridging_sum"] / ($ratedArticles + TRUST_PRIOR)
    )))
    : null;
unset($user["rated_articles"], $user["bridging_sum"]);

echo json_encode([
    "success" => true,
    "user" => $user
]);