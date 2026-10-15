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
| `comments.py` | Bridging scores for comments, using user positions from the article fit, and where they would reorder an article's top-level comments compared with like count |
| `model.py` | The `combined.py` model as importable `positions()` and `scores()`, used by `sybil.py` and `comments.py` |

## Conventions

- Articles 55 and 56 are deliberate **control posts**, one universally
  helpful and one universally unhelpful. They're excluded from user
  similarity but kept in the article ranking. They belong to their own
  account, `controlpost` (user 29), so they need no special case in
  production.
- The axis has no inherent direction, so the user in `data.ANCHOR` (YY) is
  pinned to the negative end to keep runs comparable.
- Authors' ratings of their own articles are dropped at export (and in
  production). They aren't independent
  judgements, and from an author far out on the axis they read as
  cross-side agreement.
- Comment reactions (`data/comment_ratings.tsv`) leave out commenters' reactions
  to their own comments. Deleted comments (status 0) are ignored.
- Users are matched by `user_id`. Display names come from `data/users.tsv`,
  so account renames are picked up automatically.

## Ideas

### "Needs ratings" sort (proposed 2026-10-12, not built)

A fourth front-page sort, like Community Notes' "Needs your help" queue,
that puts the articles closest to a usable score first. On 2026-10-12, 30
of 65 published articles had no ratings and 4 had one (self-ratings not
counted).

- **Simple version.** Articles with 1 rating first (one more gives them a
  score), then those with 0, newest first within each. For a logged-in
  reader, leave out articles they've already rated and their own. Needs
  only a rating count in `listArticle.php`, no new column.
- **Better version.** Two ratings from the same side do produce a score,
  but it's near 0 and says little. The scoring job could store a
  per-article certainty, such as the number of rater pairs or the spread
  between raters who agree, and the sort would put the least certain
  first. It's still a per-article value, so no user positions are stored.
- **Not personalised.** Community Notes shows a note to raters on the
  other side from its current raters. Here that would let a reader infer
  their own position from what they're shown, which breaks the rule that
  positions are never exposed.
- **Effect on the data.** Ratings from this queue are prompted rather than
  organic. If that matters for the write-up, record where each rating came
  from. Fake-account risk (`sybil.py`) is unchanged: fake accounts already
  choose their own articles.
- **UI.** A fourth option makes the cycling sort button long; a dropdown
  may be better.
