"""Sybil test: can fake accounts lift one author's articles and trust score?

Adds k fake raters who rate every published article by TARGET helpful, three ways:
  naive      no other votes
  disguised  each also copies every other vote of the user at one end of the
             axis (alternating ends), so the model spreads the fakes out
  contested  as disguised, but copies only the articles the two ends disagree
             on: the fakes still land at the ends, without boosting what both
             ends already liked
Scores follow combined.py (via model.py); the trust score follows api/user/getUser.php:
articles need 2+ ratings, score = sum / (rated + 3), shown as 50 + 250x
clamped to 0-100, and only once the author has 3+ rated articles.

    python sybil.py [ratings.tsv]
"""
import data
from model import positions, scores

TARGET = 1          # John: one rated article, so no trust score yet
SOCKS = (2, 4, 6)
SOCK_ID = 900000    # fake user ids start here
CONTROLS = data.CONTROLS
names, authors = data.names(), data.authors()


def report(label, rows):
    sc = scores(rows, positions(rows))
    real = {a: s for a, s in sc.items() if a not in CONTROLS}
    rank = lambda a: 1 + sum(s > real[a] + 1e-9 for s in real.values())
    mine = [a for a in real if authors.get(a) == TARGET]
    top = max(real, key=real.get)
    if mine:
        best = max(mine, key=real.get)
        best_txt = f"{real[best]:+.3f} #{rank(best):<2}"
    else:
        best_txt = "none scored"
    total = sum(real[a] for a in mine)
    trust = max(0, min(100, round(50 + 250 * total / (len(mine) + 3)))) if len(mine) >= 3 else "n/a"
    print(f"  {label:<14} {len(mine):>2} scored   best {best_txt:>11} of {len(real):<3} trust {trust!s:>4}"
          f"   top article {top} {real[top]:+.3f}", flush=True)


rows = data.ratings()
target = sorted(a for a, u in authors.items() if u == TARGET and a not in CONTROLS)
others = [(u, a, r) for u, a, r in rows if a not in target]

f = positions(rows)
ends = sorted((u for u in f if u != TARGET), key=f.get)
lo, hi = ends[0], ends[-1]
vote = lambda u: {a: r for uu, a, r in others if uu == u}
contested = {a for a, r in vote(lo).items() if a in vote(hi) and vote(hi)[a] != r}

print(f"Target: {names.get(TARGET, TARGET)}, articles {target}")
print(f"Axis ends copied: {names.get(lo, lo)} ({f[lo]:+.2f}) and {names.get(hi, hi)} ({f[hi]:+.2f}); "
      f"{len(contested)} articles where they disagree\n")
report("baseline", rows)

for k in SOCKS:
    socks = range(SOCK_ID, SOCK_ID + k)
    likes = [(s, a, 1) for s in socks for a in target]
    copy = lambda keep: [(s, a, r) for n, s in enumerate(socks)
                         for a, r in vote(lo if n % 2 == 0 else hi).items() if keep(a)]
    print(f"\n{k} fake accounts")
    report("naive", rows + likes)
    report("disguised", rows + likes + copy(lambda a: True))
    report("contested", rows + likes + copy(lambda a: a in contested))
