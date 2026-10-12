#!/usr/bin/env bash
# Snapshot the live ratings into data/ (read-only queries). Run from Git Bash.
set -euo pipefail
cd "$(dirname "$0")"
mkdir -p data
q() { ssh -o BatchMode=yes root@46.250.233.81 "mariadb nrs -N -B -e \"$1\""; }
# Authors' ratings of their own articles are left out, as in production
# (backend/cron/bridgingScore.php), except on the control posts.
q "SELECT r.user_id, r.article_id, r.reaction FROM article_reaction r JOIN article a USING (article_id) WHERE r.user_id <> a.author_id OR r.article_id IN (55, 56) ORDER BY r.user_id, r.article_id" > data/ratings.tsv
q "SELECT user_id, username FROM users WHERE user_id IN (SELECT user_id FROM article_reaction)" > data/users.tsv
q "SELECT article_id, LEFT(title, 70) FROM article WHERE article_id IN (SELECT article_id FROM article_reaction)" > data/titles.tsv
# Published articles only, as getUser.php counts them. The data is Singapore
# time; UTC + 8 stays right whatever timezone the server shell is in.
q "SELECT article_id, author_id FROM article WHERE status = 1 AND published_at <= UTC_TIMESTAMP() + INTERVAL 8 HOUR" > data/authors.tsv
echo "$(wc -l < data/ratings.tsv) ratings, $(wc -l < data/users.tsv) raters, $(cut -f2 data/ratings.tsv | sort -u | wc -l) articles"
