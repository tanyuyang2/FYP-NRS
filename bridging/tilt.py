"""Does the bridging ranking lean toward one side? Compare it with a plain helpful-% ranking."""
import io, runpy, contextlib
from pathlib import Path
import data
from itertools import combinations
buf = io.StringIO()
with contextlib.redirect_stdout(buf):
    g = runpy.run_path(str(Path(__file__).with_name("combined.py")), run_name="__main__")
p, items, ii, by_article, dist, C, f, names = g["p"], g["items"], g["ii"], g["by_article"], g["dist"], g["C"], g["f"], g["names"]
sign = 1 if p["fu"][g["ui"][data.ANCHOR]] < 0 else -1
fac = {a: sign * p["fi"][ii[a]] for a in items}
titles = data.titles()
def combined(a):
    v = by_article[a]; pairs = list(combinations(v, 2))
    return sum(dist[i, j] * (1 if v[i] else -1) for i, j in pairs if v[i] == v[j]) / (len(pairs) + C)
raw = {a: (sum(by_article[a].values()) + 1) / (len(by_article[a]) + 2) for a in items}  # helpful %, Laplace-smoothed
rank = lambda score: {a: n for n, a in enumerate(sorted(items, key=lambda a: -score(a)), 1)}
r_raw, r_comb, r_cn = rank(raw.get), rank(combined), rank(lambda a: p["bi"][ii[a]])
lo, hi = names[min(f, key=f.get)], names[max(f, key=f.get)]  # named after the user at each end of the axis
side = lambda a: lo if fac[a] < -0.3 else hi if fac[a] > 0.3 else ""
print(f"{'art':>4} {'side':>9} {'+/-':>5} {'raw%rank':>9} {'combined':>9} {'CN bias':>8}  title")
for a in sorted(items, key=r_comb.get):
    v = by_article[a]; pos = sum(v.values())
    print(f"{a:>4} {side(a):>9} {pos:>2}/{len(v)-pos:<2} {r_raw[a]:>9} {r_comb[a]:>9} {r_cn[a]:>8}  {titles.get(a, '')[:55]}")
for s in (lo, hi):
    arts = [a for a in items if side(a) == s]
    if arts:
        print(f"\n{s}-side articles {arts}: mean rank raw% {sum(r_raw[a] for a in arts)/len(arts):.1f}, "
              f"combined {sum(r_comb[a] for a in arts)/len(arts):.1f}, CN {sum(r_cn[a] for a in arts)/len(arts):.1f}")
