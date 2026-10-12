"""Sybil test: can fake accounts lift one author's articles and trust score?

Adds k fake raters who rate every published article by TARGET helpful, three ways:
  naive      no other votes
  disguised  each also copies every other vote of the user at one end of the
             axis (alternating ends), so the model spreads the fakes out
  contested  as disguised, but copies only the articles the two ends disagree
             on: the fakes still land at the ends, without boosting what both
             ends already liked
Scores follow combined.py; the trust score follows api/user/getUser.php:
articles need 2+ ratings, score = sum / (rated + 3), shown as 50 + 250x
clamped to 0-100, and only once the author has 3+ rated articles.

    python sybil.py [ratings.tsv]
"""
from itertools import combinations
from collections import defaultdict
import numpy as np
import data

TARGET = 1          # John: one rated article, so no trust score yet
SOCKS = (2, 4, 6)
SOCK_ID = 900000    # fake user ids start here
C, LI, LF = 6, 0.15, 0.03
CONTROLS = data.CONTROLS
names, authors = data.names(), data.authors()


def fit(U, I, R, nu, ni, seed):
    rng = np.random.default_rng(seed)
    p = {"mu": np.zeros(1), "bu": np.zeros(nu), "bi": np.zeros(ni),
         "fu": rng.normal(0, 0.1, nu), "fi": rng.normal(0, 0.1, ni)}
    m = {k: np.zeros_like(v) for k, v in p.items()}; v = {k: np.zeros_like(x) for k, x in p.items()}
    for t in range(1, 20001):
        e = p["mu"][0] + p["bu"][U] + p["bi"][I] + p["fu"][U] * p["fi"][I] - R
        g = {"mu": np.array([2 * e.sum() + 2 * LI * p["mu"][0]]),
             "bu": 2 * np.bincount(U, e, nu) + 2 * LI * p["bu"],
             "bi": 2 * np.bincount(I, e, ni) + 2 * LI * p["bi"],
             "fu": 2 * np.bincount(U, e * p["fi"][I], nu) + 2 * LF * p["fu"],
             "fi": 2 * np.bincount(I, e * p["fu"][U], ni) + 2 * LF * p["fi"]}
        for k in p:
            m[k] = 0.9 * m[k] + 0.1 * g[k]; v[k] = 0.999 * v[k] + 0.001 * g[k] ** 2
            p[k] -= 0.05 * (m[k] / (1 - 0.9 ** t)) / (np.sqrt(v[k] / (1 - 0.999 ** t)) + 1e-8)
    loss = (e ** 2).sum() + LI * (p["mu"] ** 2 + (p["bu"] ** 2).sum() + (p["bi"] ** 2).sum())[0] \
        + LF * ((p["fu"] ** 2).sum() + (p["fi"] ** 2).sum())
    return float(loss), p["fu"]


def positions(rows):
    users = sorted({u for u, _, _ in rows}); items = sorted({a for _, a, _ in rows})
    ui = {u: k for k, u in enumerate(users)}; ii = {a: k for k, a in enumerate(items)}
    U = np.array([ui[u] for u, _, _ in rows]); I = np.array([ii[a] for _, a, _ in rows])
    R = np.array([r for _, _, r in rows], float)
    fu = min((fit(U, I, R, len(users), len(items), s) for s in range(10)), key=lambda x: x[0])[1]
    return {u: fu[ui[u]] for u in users}


def scores(rows, f):
    span = max(f.values()) - min(f.values())
    by_article = defaultdict(dict)
    for u, a, r in rows:
        by_article[a][u] = r
    out = {}
    for a, votes in by_article.items():
        if len(votes) < 2:  # no pair to score: NULL in production
            continue
        pairs = list(combinations(votes, 2))
        s = sum(abs(f[i] - f[j]) / span * (1 if votes[i] else -1) for i, j in pairs if votes[i] == votes[j])
        out[a] = s / (len(pairs) + C)
    return out


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
