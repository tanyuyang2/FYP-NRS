"""Every user pair: what they actually share, and what the combined model says."""
import sys, io, runpy, contextlib
from pathlib import Path
import data
from itertools import combinations
buf = io.StringIO()
with contextlib.redirect_stdout(buf):
    g = runpy.run_path(str(Path(__file__).with_name("combined.py")), run_name="__main__")
print(buf.getvalue().splitlines()[0])
names, by_user, dist, CONTROLS, K, users = g["names"], g["by_user"], g["dist"], g["CONTROLS"], g["K"], g["users"]
out = []
for i, j in combinations(users, 2):
    common = [a for a in by_user[i] if a in by_user[j] and a not in CONTROLS]
    agree = sum(by_user[i][a] == by_user[j][a] for a in common)
    both_minus = sum(by_user[i][a] == by_user[j][a] == 0 for a in common)
    obs = (2 * agree - len(common)) / (len(common) + K)
    out.append((1 - dist[i, j], f"{names[i]} – {names[j]}", len(common), agree, len(common) - agree, both_minus, obs if common else None))
print(f"\n{'pair':<22}{'shared':>7}{'agree':>6}{'(both 👎)':>10}{'differ':>7}{'observed':>10}{'model':>7}")
for sim, pair, n, ag, dis, bm, obs in sorted(out, reverse=True):
    o = f"{obs:+.2f}" if obs is not None else "—"
    print(f"{pair:<22}{n:>7}{ag:>6}{bm:>10}{dis:>7}{o:>10}{sim:>7.2f}")
