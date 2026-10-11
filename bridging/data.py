"""Loads the files export.sh writes into data/. Pass another ratings file as argv[1] to override."""
import sys
from pathlib import Path

DATA = Path(__file__).parent / "data"
CONTROLS = {55, 56}  # "[Control Test] Universally helpful" / "[Control Post] Universally unhelpful"
ANCHOR = 26          # user whose side is pinned negative, so runs are comparable (YY)


def _tsv(name):
    with open(DATA / name, encoding="utf-8") as fh:
        return [l.rstrip("\n").split("\t") for l in fh if l.strip()]


def ratings():
    """(user_id, article_id, reaction) rows; reaction 1 = helpful, 0 = unhelpful."""
    path = Path(sys.argv[1]) if len(sys.argv) > 1 else DATA / "ratings.tsv"
    with open(path, encoding="utf-8") as fh:
        return [tuple(map(int, l.split())) for l in fh if l.strip()]


def names():
    return {int(u): n for u, n in _tsv("users.tsv")}


def titles():
    return {int(a): t for a, t in _tsv("titles.tsv")}
