#!/usr/bin/env bash
# Snapshot the live ratings into data/ (read-only queries). Run from Git Bash.
set -euo pipefail
cd "$(dirname "$0")"
mkdir -p data
q() { ssh -o BatchMode=yes root@46.250.233.81 "mariadb nrs -N -B -e \"$1\""; }
# Authors' ratings of their own articles are left out, as in production
# (backend/cron/bridgingScore.php).
q "SELECT r.user_id, r.article_id, r.reaction FROM article_reaction r JOIN article a USING (article_id) WHERE r.user_id <> a.author_id ORDER BY r.user_id, r.article_id" > data/ratings.tsv
q "SELECT user_id, username FROM users WHERE user_id IN (SELECT user_id FROM article_reaction UNION SELECT user_id FROM comment_reaction UNION SELECT user_id FROM comment)" > data/users.tsv
q "SELECT article_id, LEFT(title, 70) FROM article WHERE article_id IN (SELECT article_id FROM article_reaction)" > data/titles.tsv
# Published articles only, as getUser.php counts them. The data is Singapore
# time; UTC + 8 stays right whatever timezone the server shell is in.
q "SELECT article_id, author_id FROM article WHERE status = 1 AND published_at <= UTC_TIMESTAMP() + INTERVAL 8 HOUR" > data/authors.tsv
# Comment likes (1) and dislikes (0), minus commenters' reactions to their own
# comments, and the comments themselves (parent 0 = top level).
q "SELECT r.user_id, r.comment_id, r.reaction FROM comment_reaction r JOIN comment c USING (comment_id) WHERE r.user_id <> c.user_id ORDER BY r.user_id, r.comment_id" > data/comment_ratings.tsv
q "SELECT comment_id, article_id, user_id, COALESCE(parent_comment_id, 0), status, created_at, LEFT(REPLACE(REPLACE(REPLACE(content, CHAR(9), ' '), CHAR(10), ' '), CHAR(13), ' '), 60) FROM comment ORDER BY comment_id" > data/comments.tsv
echo "$(wc -l < data/comment_ratings.tsv) comment reactions on $(cut -f2 data/comment_ratings.tsv | sort -u | wc -l) comments"
echo "$(wc -l < data/ratings.tsv) ratings, $(cut -f1 data/ratings.tsv | sort -u | wc -l) raters, $(cut -f2 data/ratings.tsv | sort -u | wc -l) articles"
