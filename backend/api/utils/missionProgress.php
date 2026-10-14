<?php
function getMissionDefinitions()
{
    return [
        "bookmark_article" => [
            "title" => "Bookmark 1 article today",
            "target" => 1,
            "reward" => 30
        ],
		"react_articles" => [
            "title" => "React to 3 different articles today",
            "target" => 3,
            "reward" => 30
        ],
        "read_articles" => [
            "title" => "Read 5 different articles today",
            "target" => 5,
            "reward" => 20
        ],
		"receive_views" => [
			"title" => "Receive 30 views today",
			"target" => 30,
			"reward" => 40
		],
		"receive_helpful" => [
			"title" => "Receive 5 Helpful reactions today",
			"target" => 5,
			"reward" => 40
		],
		"receive_comments" => [
			"title" => "Receive 3 reader/article comments today",
			"target" => 3,
			"reward" => 40
		]
    ];
}

function getMissionDay()
{
    $start = new DateTimeImmutable(
        "today",
        new DateTimeZone("Asia/Singapore")
    );

    return [
        "date" => $start->format("Y-m-d"),
        "start" => $start->format("Y-m-d H:i:s"),
        "end" => $start->modify("+1 day")->format("Y-m-d H:i:s")
    ];
}

function getMissionReference($code, $date)
{
    return "mission:$code:$date";
}

function getMissionProgress($conn, $userId, $code, $day)
{
    switch ($code) {
        case "bookmark_article":
            $stmt = $conn->prepare(
                "SELECT COUNT(*) AS progress
                 FROM article_bookmark b
                 JOIN article a ON b.article_id = a.article_id
                 WHERE b.user_id = ?
                 AND a.author_id <> ?
                 AND b.created_at >= ?
                 AND b.created_at < ?"
            );
            break;

        case "react_articles":
            $stmt = $conn->prepare(
                "SELECT COUNT(*) AS progress
                 FROM article_reaction r
                 JOIN article a ON r.article_id = a.article_id
                 WHERE r.user_id = ?
                 AND a.author_id <> ?
                 AND r.reacted_at >= ?
                 AND r.reacted_at < ?"
            );
            break;

        case "read_articles":
            $stmt = $conn->prepare(
                "SELECT COUNT(DISTINCT v.article_id) AS progress
                 FROM article_view v
                 JOIN article a ON v.article_id = a.article_id
                 WHERE v.user_id = ?
                 AND a.author_id <> ?
                 AND v.viewed_at >= ?
                 AND v.viewed_at < ?"
            );
            break;
		
		case "receive_views":
			$stmt = $conn->prepare(
				"SELECT COUNT(*) AS progress
				 FROM article_view v
				 JOIN article a ON v.article_id = a.article_id
				 WHERE a.author_id = ?
				 AND (v.user_id IS NULL OR v.user_id <> ?)
				 AND v.viewed_at >= ?
				 AND v.viewed_at < ?"
			);
			break;

		case "receive_helpful":
			$stmt = $conn->prepare(
				"SELECT COUNT(*) AS progress
				 FROM article_reaction r
				 JOIN article a ON r.article_id = a.article_id
				 WHERE a.author_id = ?
				 AND r.user_id <> ?
				 AND r.reaction = 1
				 AND r.reacted_at >= ?
				 AND r.reacted_at < ?"
			);
			break;

		case "receive_comments":
			$stmt = $conn->prepare(
				"SELECT COUNT(DISTINCT c.user_id, c.article_id) AS progress
				 FROM comment c
				 JOIN article a ON a.article_id = c.article_id
				 WHERE a.author_id = ?
				 AND c.user_id <> ?
				 AND c.status = 1
				 AND c.created_at >= ?
				 AND c.created_at < ?"
			);
			break;

        default:
            return null;
    }

    if (!$stmt) {
        return null;
    }

    $start = $day["start"];
    $end = $day["end"];

    $stmt->bind_param("iiss", $userId, $userId, $start, $end);

    if (!$stmt->execute()) {
        return null;
    }

    $result = $stmt->get_result();
    $stmt->close();
    $row = $result->fetch_assoc();

    return (int) $row["progress"];
}

function isMissionClaimed($conn, $userId, $referenceKey)
{
    $stmt = $conn->prepare(
        "SELECT 1
         FROM `transaction`
         WHERE user_id = ? AND reference_key = ?"
    );

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("is", $userId, $referenceKey);

	if (!$stmt->execute()) {
		return null;
    }

    $result = $stmt->get_result();
    $stmt->close();
    $claimed = $result->num_rows > 0;

    return $claimed;
}