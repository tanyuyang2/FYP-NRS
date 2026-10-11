"""User positions from each of combined.py's 10 fits: do near-equal fits agree?"""
import runpy, io, contextlib
from pathlib import Path
import data
with contextlib.redirect_stdout(io.StringIO()):
    g = runpy.run_path(str(Path(__file__).with_name("combined.py")))
names, ui, users = g["names"], g["ui"], g["users"]
for loss, q in g["fits"]:
    fu = q["fu"] * (1 if q["fu"][ui[data.ANCHOR]] < 0 else -1)
    print(f"loss {loss:.4f}  " + "  ".join(f"{names[u]}:{fu[ui[u]]:+.2f}" for u in sorted(users, key=lambda u: fu[ui[u]])))
