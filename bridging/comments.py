"""Bridging scores for comments: would they reorder an article's comments?

User positions come from the article fit (combined.py's model), because
article ratings place users far better than the few comment reactions do.
Each comment is then scored like an article: sum of +-distance over pairs of
reactors who agreed, / (pairs + 6), with distance on the article fit's scale.
Reactions from users with no article ratings have no position and are
dropped; commenters' reactions to their own comments are dropped at export.

As a check, it refits with comment reactions added as extra items, to see
whether they would move anyone.

    python comments.py [ratings.tsv]
"""
from collections import defaultdict
import data
from model import positions, scores

names, comments = data.names(), data.comments()
article_rows = data.ratings()
comment_rows = [(u, c, r) for u, c, r in data.comment_ratings() if comments.get(c, {}).get("status") == 1]

f = positions(article_rows)
if f.get(data.ANCHOR, 0) > 0:  # sign is arbitrary; pin as combined.py does
    f = {u: -x for u, x in f.items()}
span = max(f.values()) - min(f.values())
art = scores(article_rows, f)
print("Article fit check (should match the live bridging_score): "
      + ", ".join(f"{a} {s:+.3f}" for a, s in sorted(art.items(), key=lambda x: -x[1])[:3]))

reactors = sorted({u for u, _, _ in comment_rows})
placed = [u for u in reactors if u in f]
print(f"\n{len(comment_rows)} reactions on {len({c for _, c, _ in comment_rows})} live comments, "
      f"from {len(reactors)} users; {len(placed)} have a position")
for u in sorted(reactors, key=lambda u: f.get(u, 9)):
    n = sum(1 for uu, _, _ in comment_rows if uu == u)
    pos = f"{f[u]:+.2f}" if u in f else "  none"
    print(f"  {names.get(u, u):>14} {pos}   {n} reactions")

sc = scores(comment_rows, f, span)
votes = defaultdict(dict)
for u, c, r in comment_rows:
    votes[c][u] = r

print(f"\nComments with reactions (score / (pairs + 6); '-' = fewer than 2 placed reactors)")
print(f"  {'comment':>7} {'art':>4} {'kind':<5} {'+/-':>4}  {'score':>6}  reactors / text")
for c in sorted(votes, key=lambda c: (-sc.get(c, -9), -sum(votes[c].values()))):
    m = comments[c]
    up = sum(votes[c].values()); down = len(votes[c]) - up
    who = ", ".join(f"{names.get(u, u)}{'+' if r else '-'}" for u, r in votes[c].items())
    s = f"{sc[c]:+.3f}" if c in sc else "     -"
    print(f"  {c:>7} {m['article']:>4} {'reply' if m['parent'] else 'top':<5} {up}/{down:<2}  {s}  "
          f"{who}\n{'':>34}\"{m['text']}\"")

print("\nTop-level order per article, where bridging could change it")
by_article = defaultdict(list)
for c, m in comments.items():
    if m["status"] == 1 and not m["parent"]:
        by_article[m["article"]].append(c)
changed = 0
for a, cs in sorted(by_article.items()):
    if sum(c in sc for c in cs) == 0:
        continue
    oldest = sorted(cs, key=lambda c: comments[c]["created"])
    likes = sorted(oldest, key=lambda c: -sum(votes[c].values()))          # stable: ties stay oldest first
    bridged = sorted(oldest, key=lambda c: -sc[c] if c in sc else 9)        # unscored after scored, oldest first
    changed += bridged != likes
    print(f"  article {a}: oldest {oldest}\n{'':>14}likes  {likes}\n{'':>14}bridge {bridged}"
          + ("   <- differs from likes" if bridged != likes else ""))
print(f"  {changed} article(s) where bridging orders comments differently from like count")

both = [(u, ("a", a), r) for u, a, r in article_rows] + [(u, ("c", c), r) for u, c, r in comment_rows]
g = positions(both)
sign = 1 if sum(f[u] * g[u] for u in f) >= 0 else -1  # the axis sign is arbitrary
print(f"\nRefit with comment reactions as extra items (axis span {span:.2f}):")
for u in sorted(f, key=f.get):
    print(f"  {names.get(u, u):>14} {f[u]:+.2f} -> {sign * g[u]:+.2f}")
