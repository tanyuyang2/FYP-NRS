"""Community Notes-style matrix factorisation on article_reaction.

rating ~ mu + user_bias + item_bias + user_factor * item_factor
Regularise intercepts 5x harder than factors (CN: 0.15 / 0.03), so a rating
pattern explainable by "which side you're on" goes into the factor, and only
agreement ACROSS sides survives into item_bias -- the bridging score.
"""
import sys
import numpy as np
import data

rows = data.ratings()
users = sorted({u for u, _, _ in rows})
items = sorted({a for _, a, _ in rows})
ui = {u: k for k, u in enumerate(users)}
ii = {a: k for k, a in enumerate(items)}
U = np.array([ui[u] for u, _, _ in rows])
I = np.array([ii[a] for _, a, _ in rows])
R = np.array([r for _, _, r in rows], float)

LI, LF = 0.15, 0.03


def fit(seed):
    rng = np.random.default_rng(seed)
    p = {
        "mu": np.zeros(1),
        "bu": np.zeros(len(users)), "bi": np.zeros(len(items)),
        "fu": rng.normal(0, 0.1, len(users)), "fi": rng.normal(0, 0.1, len(items)),
    }
    m = {k: np.zeros_like(v) for k, v in p.items()}
    v = {k: np.zeros_like(x) for k, x in p.items()}
    lr, b1, b2 = 0.05, 0.9, 0.999
    for t in range(1, 20001):
        pred = p["mu"][0] + p["bu"][U] + p["bi"][I] + p["fu"][U] * p["fi"][I]
        e = pred - R
        g = {
            "mu": np.array([2 * e.sum() + 2 * LI * p["mu"][0]]),
            "bu": 2 * np.bincount(U, e, len(users)) + 2 * LI * p["bu"],
            "bi": 2 * np.bincount(I, e, len(items)) + 2 * LI * p["bi"],
            "fu": 2 * np.bincount(U, e * p["fi"][I], len(users)) + 2 * LF * p["fu"],
            "fi": 2 * np.bincount(I, e * p["fu"][U], len(items)) + 2 * LF * p["fi"],
        }
        for k in p:
            m[k] = b1 * m[k] + (1 - b1) * g[k]
            v[k] = b2 * v[k] + (1 - b2) * g[k] ** 2
            p[k] -= lr * (m[k] / (1 - b1 ** t)) / (np.sqrt(v[k] / (1 - b2 ** t)) + 1e-8)
    loss = (e ** 2).sum() + LI * (p["mu"] ** 2 + (p["bu"] ** 2).sum() + (p["bi"] ** 2).sum()) \
        + LF * ((p["fu"] ** 2).sum() + (p["fi"] ** 2).sum())
    return float(loss[0]), p


loss, p = min((fit(s) for s in range(10)), key=lambda x: x[0])
print(f"ratings={len(R)} users={len(users)} articles={len(items)} loss={loss:.3f} mu={p['mu'][0]:.3f}\n")

pos = np.bincount(I, R, len(items)).astype(int)
n = np.bincount(I, minlength=len(items))
print(f"{'article':>7} {'+':>3} {'-':>3} {'raw%':>5} {'bridge':>7} {'factor':>7}  status (CN thresholds)")
for k in np.argsort(-p["bi"]):
    b, f = p["bi"][k], p["fi"][k]
    status = "HELPFUL" if b >= 0.40 and abs(f) < 0.5 else \
             "NOT HELPFUL" if b <= -0.05 - 0.8 * abs(f) else "needs more ratings"
    print(f"{items[k]:>7} {pos[k]:>3} {n[k]-pos[k]:>3} {100*pos[k]/n[k]:>4.0f}% {b:>7.3f} {f:>7.3f}  {status}")

print(f"\n{'user':>5} {'bias':>7} {'factor':>7}")
for k in np.argsort(p["fu"]):
    print(f"{users[k]:>5} {p['bu'][k]:>7.3f} {p['fu'][k]:>7.3f}")
