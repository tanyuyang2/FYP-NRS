# Bridging analysis

Offline research scripts for ranking articles by **bridging**: an article
scores well when it's rated helpful by people who usually disagree with
each other, not just by many people. The design follows Community Notes
(Birdwatch). These scripts are the reference the production version gets
checked against: `backend/cron/bridgingScore.php` ports `combined.py` and
feeds the author trust score. Run it with an export to compare:
`php backend/cron/bridgingScore.php bridging/data/ratings.tsv`. It leaves
articles with a single rating unscored, where `combined.py` prints 0.

## Running

```bash
bash export.sh          # snapshot live ratings into data/ (read-only, over SSH)
python combined.py      # main model: user positions, similarities, article ranking
```

`data/` is gitignored because it holds per-user votes. Every script reads
`data/ratings.tsv`, or a ratings file given as the first argument.

## Scripts

| Script | What it shows |
|---|---|
| `bridging.py` | Community Notes matrix factorisation (rating ≈ μ + b_user + b_article + f_user·f_article). b_article is the bridging score. The thresholds are CN's: helpful ≥ 0.40, not helpful ≤ −0.05 − 0.8·\|f\|. |
| `combined.py` | The main model: MF positions give user-to-user distances. An article's score sums ±distance over rater pairs who voted the same way, then divides by (pairs + 6). |
| `pairs.py` | Every user pair: shared articles, agreements, observed vs model similarity |
| `sides.py` | Which articles define the axis, and how each side voted on them |
| `tilt.py` | Whether the ranking favours either side, compared with plain helpful % |
| `stability.py` | User positions from each of the 10 fits, to spot users the data can't place |
| `sybil.py` | Whether fake accounts can lift one author's articles and trust score: naive likers vs accounts that copy real users' votes to look independent |

## Conventions

- Articles 55 and 56 are deliberate **control posts**, one universally
  helpful and one universally unhelpful. They're excluded from user
  similarity but kept in the article ranking.
- The axis has no inherent direction, so the user in `data.ANCHOR` (YY) is
  pinned to the negative end to keep runs comparable.
- Authors' ratings of their own articles are dropped at export (and in
  production), except on the control posts. They aren't independent
  judgements, and from an author far out on the axis they read as
  cross-side agreement.
- Users are matched by `user_id`. Display names come from `data/users.tsv`,
  so account renames are picked up automatically.
