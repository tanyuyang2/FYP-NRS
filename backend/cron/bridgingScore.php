<?php
/*
 * Bridging score for every rated article: the pairwise model from
 * bridging/combined.py, which stays the reference this is checked against.
 *
 * 1. Fit rating ~ mu + b_user + b_article + f_user * f_article with Community
 *    Notes regularisation, which places every user on one learned axis.
 * 2. distance(i, j) = |f_i - f_j| / (max f - min f), in [0, 1].
 * 3. Article score = sum over rater pairs who voted the same way of
 *    (+distance if both helpful, -distance if both unhelpful) / (pairs + 6).
 *    Agreement only counts when it comes from people far apart on the axis.
 *
 * Run by the nrs-bridging systemd timer. It writes article.bridging_score and
 * nothing else: user positions are never stored, because they amount to a
 * guess at which side each reader is on.
 *
 *   php bridgingScore.php               score the live ratings and save them
 *   php bridgingScore.php ratings.tsv   print scores for an export; no database
 *
 * bridging/export.sh applies the same self-rating filter as the query below,
 * so both modes see the same ratings.
 */

if (PHP_SAPI !== "cli") {
	http_response_code(404);
	exit;
}

const PAIR_PRIOR = 6;        // pseudo-pairs that pull thinly rated articles towards 0
const REG_INTERCEPT = 0.15;  // Community Notes: intercepts regularised 5x harder
const REG_FACTOR = 0.03;     // than factors
const FIT_STEPS = 20000;
const FIT_SEEDS = 10;

/*
 * One Adam fit of the factor model. $U, $I and $R are parallel arrays of
 * user index, article index and rating. Returns [loss, user factors].
 */
function fitFactors(array $U, array $I, array $R, int $nUsers, int $nItems, int $seed): array
{
	mt_srand($seed);
	$normal = function () {
		$a = (mt_rand() + 1) / (mt_getrandmax() + 1);
		$b = mt_rand() / mt_getrandmax();
		return 0.1 * sqrt(-2 * log($a)) * cos(2 * M_PI * $b);
	};

	$mu = 0.0;
	$bu = array_fill(0, $nUsers, 0.0);
	$bi = array_fill(0, $nItems, 0.0);
	$fu = [];
	$fi = [];
	for ($k = 0; $k < $nUsers; $k++) {
		$fu[] = $normal();
	}
	for ($k = 0; $k < $nItems; $k++) {
		$fi[] = $normal();
	}

	// Adam state, one pair of moment vectors per parameter vector.
	$zeroU = array_fill(0, $nUsers, 0.0);
	$zeroI = array_fill(0, $nItems, 0.0);
	$mMu = $vMu = 0.0;
	$mBu = $vBu = $mFu = $vFu = $zeroU;
	$mBi = $vBi = $mFi = $vFi = $zeroI;

	$n = count($R);
	for ($t = 1; $t <= FIT_STEPS; $t++) {
		$gMu = 0.0;
		$gBu = $gFu = $zeroU;
		$gBi = $gFi = $zeroI;
		for ($k = 0; $k < $n; $k++) {
			$u = $U[$k];
			$i = $I[$k];
			$e = $mu + $bu[$u] + $bi[$i] + $fu[$u] * $fi[$i] - $R[$k];
			$gMu += $e;
			$gBu[$u] += $e;
			$gBi[$i] += $e;
			$gFu[$u] += $e * $fi[$i];
			$gFi[$i] += $e * $fu[$u];
		}

		$c1 = 1 - 0.9 ** $t;
		$c2 = 1 - 0.999 ** $t;

		$g = 2 * $gMu + 2 * REG_INTERCEPT * $mu;
		$mMu = 0.9 * $mMu + 0.1 * $g;
		$vMu = 0.999 * $vMu + 0.001 * $g * $g;
		$mu -= 0.05 * ($mMu / $c1) / (sqrt($vMu / $c2) + 1e-8);

		for ($k = 0; $k < $nUsers; $k++) {
			$g = 2 * $gBu[$k] + 2 * REG_INTERCEPT * $bu[$k];
			$mBu[$k] = 0.9 * $mBu[$k] + 0.1 * $g;
			$vBu[$k] = 0.999 * $vBu[$k] + 0.001 * $g * $g;
			$g2 = 2 * $gFu[$k] + 2 * REG_FACTOR * $fu[$k];
			$mFu[$k] = 0.9 * $mFu[$k] + 0.1 * $g2;
			$vFu[$k] = 0.999 * $vFu[$k] + 0.001 * $g2 * $g2;
			$bu[$k] -= 0.05 * ($mBu[$k] / $c1) / (sqrt($vBu[$k] / $c2) + 1e-8);
			$fu[$k] -= 0.05 * ($mFu[$k] / $c1) / (sqrt($vFu[$k] / $c2) + 1e-8);
		}
		for ($k = 0; $k < $nItems; $k++) {
			$g = 2 * $gBi[$k] + 2 * REG_INTERCEPT * $bi[$k];
			$mBi[$k] = 0.9 * $mBi[$k] + 0.1 * $g;
			$vBi[$k] = 0.999 * $vBi[$k] + 0.001 * $g * $g;
			$g2 = 2 * $gFi[$k] + 2 * REG_FACTOR * $fi[$k];
			$mFi[$k] = 0.9 * $mFi[$k] + 0.1 * $g2;
			$vFi[$k] = 0.999 * $vFi[$k] + 0.001 * $g2 * $g2;
			$bi[$k] -= 0.05 * ($mBi[$k] / $c1) / (sqrt($vBi[$k] / $c2) + 1e-8);
			$fi[$k] -= 0.05 * ($mFi[$k] / $c1) / (sqrt($vFi[$k] / $c2) + 1e-8);
		}
	}

	$loss = REG_INTERCEPT * $mu * $mu;
	for ($k = 0; $k < $n; $k++) {
		$e = $mu + $bu[$U[$k]] + $bi[$I[$k]] + $fu[$U[$k]] * $fi[$I[$k]] - $R[$k];
		$loss += $e * $e;
	}
	for ($k = 0; $k < $nUsers; $k++) {
		$loss += REG_INTERCEPT * $bu[$k] ** 2 + REG_FACTOR * $fu[$k] ** 2;
	}
	for ($k = 0; $k < $nItems; $k++) {
		$loss += REG_INTERCEPT * $bi[$k] ** 2 + REG_FACTOR * $fi[$k] ** 2;
	}

	return [$loss, $fu];
}

/*
 * $rows: [user_id, article_id, reaction] with reaction 1 = helpful,
 * 0 = unhelpful. Returns [article_id => score] for every article with at
 * least two ratings.
 */
function bridgingScores(array $rows): array
{
	$userIndex = [];
	$itemIndex = [];
	$U = $I = $R = [];
	foreach ($rows as [$user, $article, $reaction]) {
		$U[] = $userIndex[$user] ??= count($userIndex);
		$I[] = $itemIndex[$article] ??= count($itemIndex);
		$R[] = (float) $reaction;
	}
	if (!$R) {
		return [];
	}

	$best = null;
	for ($seed = 0; $seed < FIT_SEEDS; $seed++) {
		$fit = fitFactors($U, $I, $R, count($userIndex), count($itemIndex), $seed);
		if ($best === null || $fit[0] < $best[0]) {
			$best = $fit;
		}
	}
	$f = $best[1];
	// Distances only use |f_i - f_j|, so the axis's arbitrary sign does not matter.
	$span = max($f) - min($f);

	$votes = [];
	foreach ($rows as $k => [, $article]) {
		$votes[$article][] = [$f[$U[$k]], (int) $R[$k]];
	}

	$scores = [];
	foreach ($votes as $article => $raters) {
		$n = count($raters);
		// One rating has no pair to score. combined.py prints 0 for it; here it
		// stays NULL, so a brand-new article does not count as rated yet.
		if ($n < 2) {
			continue;
		}
		$sum = 0.0;
		for ($a = 0; $a < $n; $a++) {
			for ($b = $a + 1; $b < $n; $b++) {
				if ($raters[$a][1] !== $raters[$b][1] || $span <= 0) {
					continue;
				}
				$distance = abs($raters[$a][0] - $raters[$b][0]) / $span;
				$sum += $raters[$a][1] === 1 ? $distance : -$distance;
			}
		}
		$scores[$article] = $sum / ($n * ($n - 1) / 2 + PAIR_PRIOR);
	}

	return $scores;
}

$started = microtime(true);

if (isset($argv[1])) {
	$rows = [];
	foreach (file($argv[1], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
		$rows[] = array_map("intval", preg_split('/\s+/', trim($line)));
	}
	$scores = bridgingScores($rows);
	arsort($scores);
	foreach ($scores as $article => $score) {
		printf("%d\t%+.3f\n", $article, $score);
	}
	exit;
}

require __DIR__ . "/../config/database.php";

// An author's rating of their own article is not an independent judgement:
// it is helpful whichever side they are on, and from an author far out on the
// axis it reads as cross-side agreement. The control posts keep theirs.
$result = $conn->query(
	"SELECT r.user_id, r.article_id, r.reaction
	 FROM article_reaction r
	 JOIN article a ON a.article_id = r.article_id
	 WHERE r.user_id <> a.author_id OR r.article_id IN (55, 56)"
);
if (!$result) {
	fwrite(STDERR, "reading ratings failed: {$conn->error}\n");
	exit(1);
}
$rows = $result->fetch_all(MYSQLI_NUM);
$scores = bridgingScores($rows);

// One transaction, so readers see either the previous run's scores or this
// run's, never a mix. Articles that lost all their ratings go back to NULL.
$conn->begin_transaction();
$ok = $conn->query(
	"UPDATE article
	 SET bridging_score = NULL, bridging_calculated_at = NULL
	 WHERE bridging_score IS NOT NULL"
);
$stmt = $ok ? $conn->prepare(
	"UPDATE article
	 SET bridging_score = ?, bridging_calculated_at = NOW()
	 WHERE article_id = ?"
) : false;
if ($stmt) {
	foreach ($scores as $article => $score) {
		$stmt->bind_param("di", $score, $article);
		if (!$stmt->execute()) {
			$ok = false;
			break;
		}
	}
	$stmt->close();
}
if (!$ok || !$stmt) {
	fwrite(STDERR, "saving scores failed: {$conn->error}\n");
	$conn->rollback();
	exit(1);
}
$conn->commit();

printf(
	"scored %d articles from %d ratings in %.1fs\n",
	count($scores),
	count($rows),
	microtime(true) - $started
);
