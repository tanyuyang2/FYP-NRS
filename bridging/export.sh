#!/usr/bin/env bash
# Snapshot the live ratings into data/ (read-only queries). Run from Git Bash.
set -euo pipefail
cd "$(dirname "$0")"
mkdir -p data
q() { ssh -o BatchMode=yes root@46.250.233.81 "mariadb nrs -N -B -e \"$1\""; }
q "SELECT user_id, article_id, reaction FROM article_reaction ORDER BY user_id, article_id" > data/ratings.tsv
q "SELECT user_id, username FROM users WHERE user_id IN (SELECT user_id FROM article_reaction)" > data/users.tsv
q "SELECT article_id, LEFT(title, 70) FROM article WHERE article_id IN (SELECT article_id FROM article_reaction)" > data/titles.tsv
echo "$(wc -l < data/ratings.tsv) ratings, $(wc -l < data/users.tsv) raters, $(wc -l < data/titles.tsv) articles"
