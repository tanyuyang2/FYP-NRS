<?php
/*
 * Trending score for every published article, from the last 48 hours:
 * views + 3 x bookmarks + 2 x helpful reactions.
 *
 * Run by the nrs-trending systemd timer every 4 hours. It used to be an
 * unauthenticated endpoint that Sidebar.jsx POSTed on every page load, so
 * each visitor ran this UPDATE across the whole article table.
 *
 * Every published article is rewritten in the same statement, so they all
 * share one score_calculated_at: listTrending.php shows only the latest run.
 * An article with no views in the window drops to 0, rather than keeping
 * the score from the last time it had some.
 */

if (PHP_SAPI !== "cli") {
	http_response_code(404);
	exit;
}

require __DIR__ . "/../config/database.php";

$ok = $conn->query(
	"UPDATE article a
	 LEFT JOIN (
		SELECT article_id, COUNT(*) AS recent_views
		FROM article_view
		WHERE viewed_at >= NOW() - INTERVAL 48 HOUR
		GROUP BY article_id
	 ) av ON av.article_id = a.article_id
	 LEFT JOIN (
		SELECT article_id, COUNT(*) AS recent_bookmarks
		FROM article_bookmark
		WHERE created_at >= NOW() - INTERVAL 48 HOUR
		GROUP BY article_id
	 ) bookmark ON bookmark.article_id = a.article_id
	 LEFT JOIN (
		SELECT article_id, COUNT(*) AS recent_helpful_reactions
		FROM article_reaction
		WHERE reaction = 1
		AND reacted_at >= NOW() - INTERVAL 48 HOUR
		GROUP BY article_id
	 ) reaction ON reaction.article_id = a.article_id
	 SET
		a.trending_score = IF(av.article_id IS NULL, 0,
			av.recent_views
			+ COALESCE(bookmark.recent_bookmarks, 0) * 3
			+ COALESCE(reaction.recent_helpful_reactions, 0) * 2),
		a.score_calculated_at = NOW()
	 WHERE a.status = 1
	 AND a.published_at <= NOW()"
);
if (!$ok) {
	fwrite(STDERR, "calculating trending scores failed: {$conn->error}\n");
	exit(1);
}
// score_calculated_at changes on every row, so this counts every article scored.
$scored = $conn->affected_rows;

$trending = $conn->query("SELECT COUNT(*) FROM article WHERE status = 1 AND trending_score > 0")
	->fetch_row()[0];
printf("scored %d articles, %d trending\n", $scored, $trending);
