"""Which articles define the model's axis, and how each side voted on them."""
import io, runpy, contextlib
from pathlib import Path
import data
buf = io.StringIO()
with contextlib.redirect_stdout(buf):
    g = runpy.run_path(str(Path(__file__).with_name("combined.py")), run_name="__main__")
p, items, ii, names, by_article, f = g["p"], g["items"], g["ii"], g["names"], g["by_article"], g["f"]
sign = 1 if p["fu"][g["ui"][data.ANCHOR]] < 0 else -1  # same pin as combined.py
titles = data.titles()
neg = [u for u in f if f[u] < 0]; pos = [u for u in f if f[u] > 0]
lo, hi = f"{names[min(f, key=f.get)]} side", f"{names[max(f, key=f.get)]} side"
print(f"{lo}:", ", ".join(names[u] for u in sorted(neg, key=f.get)))
print(f"{hi}:", ", ".join(names[u] for u in sorted(pos, key=f.get)))
print(f"\n{'art':>4} {'factor':>7} {'bias':>6}  {lo:<26} {hi:<30} title")
def votes(a, side):
    return " ".join(f"{names[u]}{'+' if by_article[a][u] else '-'}" for u in sorted(side, key=f.get) if u in by_article[a])
for a in sorted(items, key=lambda a: sign * p["fi"][ii[a]]):
    k = ii[a]
    print(f"{a:>4} {sign*p['fi'][k]:>+7.2f} {p['bi'][k]:>+6.2f}  {votes(a,neg):<26} {votes(a,pos):<30} {titles.get(a, '')}")
