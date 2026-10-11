"""Combined: matrix-factorisation positions + pairwise scoring + confidence shrinkage.

1. Fit rating ~ mu + b_user + b_article + f_user * f_article (Community Notes
   regularisation) to place every user on one learned axis. Users who never
   co-rated anything still get positions, via the people they DID overlap with.
2. distance(i, j) = |f_i - f_j| / (max f - min f)   in [0, 1]; similarity = 1 - distance
3. article score = sum over rater pairs who voted the same way of
   (+distance if both helpful, -distance if both unhelpful), / (pairs + C)
"""
import sys
from itertools import combinations
from collections import defaultdict
import numpy as np
import data

C, K, LI, LF = 6, 2, 0.15, 0.03
CONTROLS, ANCHOR = data.CONTROLS, data.ANCHOR
names = data.names()

rows = data.ratings()
users = sorted({u for u, _, _ in rows}); items = sorted({a for _, a, _ in rows})
ui = {u: k for k, u in enumerate(users)}; ii = {a: k for k, a in enumerate(items)}
U = np.array([ui[u] for u, _, _ in rows]); I = np.array([ii[a] for _, a, _ in rows])
R = np.array([r for _, _, r in rows], float)

def fit(seed):
    rng = np.random.default_rng(seed)
    p = {"mu": np.zeros(1), "bu": np.zeros(len(users)), "bi": np.zeros(len(items)),
         "fu": rng.normal(0, 0.1, len(users)), "fi": rng.normal(0, 0.1, len(items))}
    m = {k: np.zeros_like(v) for k, v in p.items()}; v = {k: np.zeros_like(x) for k, x in p.items()}
    for t in range(1, 20001):
        e = p["mu"][0] + p["bu"][U] + p["bi"][I] + p["fu"][U] * p["fi"][I] - R
        g = {"mu": np.array([2 * e.sum() + 2 * LI * p["mu"][0]]),
             "bu": 2 * np.bincount(U, e, len(users)) + 2 * LI * p["bu"],
             "bi": 2 * np.bincount(I, e, len(items)) + 2 * LI * p["bi"],
             "fu": 2 * np.bincount(U, e * p["fi"][I], len(users)) + 2 * LF * p["fu"],
             "fi": 2 * np.bincount(I, e * p["fu"][U], len(items)) + 2 * LF * p["fi"]}
        for k in p:
            m[k] = 0.9 * m[k] + 0.1 * g[k]; v[k] = 0.999 * v[k] + 0.001 * g[k] ** 2
            p[k] -= 0.05 * (m[k] / (1 - 0.9 ** t)) / (np.sqrt(v[k] / (1 - 0.999 ** t)) + 1e-8)
    loss = (e ** 2).sum() + LI * (p["mu"] ** 2 + (p["bu"] ** 2).sum() + (p["bi"] ** 2).sum())[0] \
        + LF * ((p["fu"] ** 2).sum() + (p["fi"] ** 2).sum())
    return float(loss), p

fits = sorted((fit(s) for s in range(10)), key=lambda x: x[0])
p = fits[0][1]
f = {u: p["fu"][ui[u]] for u in users}
if f[ANCHOR] > 0:  # sign is arbitrary; pin the anchor user to the negative end so runs are comparable
    f = {u: -x for u, x in f.items()}
span = max(f.values()) - min(f.values())
dist = {(i, j): abs(f[i] - f[j]) / span for i in users for j in users}

by_user, by_article = defaultdict(dict), defaultdict(dict)
for u, a, r in rows:
    by_user[u][a] = r; by_article[a][u] = r

# stability: do the other near-optimal fits put users in the same order?
orders = []
for loss, q in fits[:5]:
    g = q["fu"] * (1 if q["fu"][ui[ANCHOR]] < 0 else -1)
    orders.append(tuple(users[k] for k in np.argsort(g)))
print(f"best loss {fits[0][0]:.3f}; top-5 fits agree on user order: {len(set(orders)) == 1}")

print("\nUser positions on the learned axis")
for u in sorted(users, key=lambda u: f[u]):
    n_real = sum(1 for a in by_user[u] if a not in CONTROLS)
    print(f"  {names[u]:>10} {f[u]:+.3f}   ({len(by_user[u])} ratings, {n_real} excluding controls)")

print("\nSimilarity = 1 - distance   [observed: agreement on shared non-control articles, (n)]")
order = sorted(users, key=lambda u: f[u])
print(" " * 11 + "".join(f"{names[u]:>18}" for u in order))
for i in order:
    cells = []
    for j in order:
        if i == j:
            cells.append(f"{'—':>18}"); continue
        common = [a for a in by_user[i] if a in by_user[j] and a not in CONTROLS]
        obs = f"{(2*sum(by_user[i][a]==by_user[j][a] for a in common)-len(common))/(len(common)+K):+.2f}({len(common)})" if common else "none"
        cells.append(f"{1-dist[i, j]:>6.2f} [{obs:>9}]")
    print(f"{names[i]:>10} " + "".join(cells))

print("\nNearest / farthest")
for i in order:
    others = sorted((u for u in users if u != i), key=lambda j: dist[i, j])
    print(f"  {names[i]:>10}: " + " > ".join(f"{names[j]} {1-dist[i,j]:.2f}" for j in others))

print(f"\nArticles (score / (pairs + {C}))")
out = []
for a, votes in by_article.items():
    pairs = list(combinations(votes, 2))
    s = sum(dist[i, j] * (1 if votes[i] else -1) for i, j in pairs if votes[i] == votes[j])
    pos = sum(votes.values())
    out.append((s / (len(pairs) + C), a, pos, len(votes) - pos))
for n, (s, a, pos, neg) in enumerate(sorted(out, reverse=True), 1):
    print(f"  #{n:<2} {a:>3}  {pos}/{neg}  {s:+.3f}")
