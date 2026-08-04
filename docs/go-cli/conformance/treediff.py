#!/usr/bin/env python3
"""Diff two authoritative command trees."""
import json
import sys

go = json.load(open(sys.argv[1]))
ts = json.load(open(sys.argv[2]))

# `help` and `completion` are framework plumbing, not ported surface.
NOISE = {'help', 'completion'}


def paths(tree):
    return {p for p in tree
            if not any(part in NOISE for part in p.split())}


gp, tp = paths(go), paths(ts)
missing, extra, shared = sorted(tp - gp), sorted(gp - tp), sorted(gp & tp)

print(f'commands: go={len(gp)} ts={len(tp)} shared={len(shared)}')
print(f'\n## {len(missing)} in TS, absent from Go')
for c in missing:
    print(' ', c)
print(f'\n## {len(extra)} in Go, absent from TS')
for c in extra:
    print(' ', c)

# Persistent flags are repeated on every page by both completion generators;
# subtract each root's own set so per-command sets compare like for like.
gg, tg = set(go['(root)']['flags']), set(ts['(root)']['flags'])
print('\n## global flags')
print('  only TS:', sorted(tg - gg))
print('  only Go:', sorted(gg - tg))

print('\n## flag differences on shared commands')
gaps = 0
for c in shared:
    lost = sorted((set(ts[c]['flags']) - tg) - (set(go[c]['flags']) - gg))
    gained = sorted((set(go[c]['flags']) - gg) - (set(ts[c]['flags']) - tg))
    if lost or gained:
        gaps += 1
        print(f'  {c}')
        if lost:
            print(f'    missing in Go: {lost}')
        if gained:
            print(f'    extra in Go:   {gained}')
print(f'  ({gaps} of {len(shared)} shared commands differ)')
