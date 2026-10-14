"""The combined.py model as importable functions, for scripts that need user
positions or pairwise scores without combined.py's report.

combined.py stays the standalone reference; these must match it.
"""
from itertools import combinations
from collections import defaultdict
import numpy as np

C, LI, LF = 6, 0.15, 0.03


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
    """user_id -> position on the learned axis, from (user, item, rating) rows; best of 10 fits."""
    users = sorted({u for u, _, _ in rows}); items = sorted({a for _, a, _ in rows})
    ui = {u: k for k, u in enumerate(users)}; ii = {a: k for k, a in enumerate(items)}
    U = np.array([ui[u] for u, _, _ in rows]); I = np.array([ii[a] for _, a, _ in rows])
    R = np.array([r for _, _, r in rows], float)
    fu = min((fit(U, I, R, len(users), len(items), s) for s in range(10)), key=lambda x: x[0])[1]
    return {u: fu[ui[u]] for u in users}


def scores(rows, f, span=None):
    """item -> pairwise bridging score for items with 2+ raters who have a position in f.

    span defaults to the spread of f, so distances are on the same scale as
    the article scores when f came from the article fit.
    """
    span = span or max(f.values()) - min(f.values())
    by_item = defaultdict(dict)
    for u, a, r in rows:
        if u in f:
            by_item[a][u] = r
    out = {}
    for a, votes in by_item.items():
        if len(votes) < 2:  # no pair to score: NULL in production
            continue
        pairs = list(combinations(votes, 2))
        s = sum(abs(f[i] - f[j]) / span * (1 if votes[i] else -1) for i, j in pairs if votes[i] == votes[j])
        out[a] = s / (len(pairs) + C)
    return out
