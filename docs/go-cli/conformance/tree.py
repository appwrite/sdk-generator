#!/usr/bin/env python3
"""Extract each CLI's authoritative command tree.

Help text cannot be parsed reliably -- neither CLI reports an unknown
subcommand, and cobra pads the longest entry with a single space -- so both
sides come from the completion machinery instead, which is generated from the
command definitions themselves.

  ts <completion.bash>   parse the emitted bash script's context table
  go <binary>            walk cobra's hidden __complete
"""
import json
import re
import subprocess
import sys

CONTEXT = re.compile(r"^\s*'([^']*)'\)\s*$")
COMPLETIONS = re.compile(r"^\s*completions='([^']*)'\s*$")


def from_bash(path):
    """{command path: {'subs': [...], 'flags': [...]}} from the bash script."""
    tree, context = {}, None
    for line in open(path):
        m = CONTEXT.match(line)
        if m:
            context = m.group(1) or '(root)'
            continue
        m = COMPLETIONS.match(line)
        if m and context is not None:
            words = m.group(1).split()
            tree[context] = {
                'subs': sorted(w for w in words if not w.startswith('-')),
                'flags': sorted(w[2:] for w in words if w.startswith('--')),
            }
            context = None
    return tree


def complete(binary, path, suffix):
    p = subprocess.run([binary, '__complete'] + path + [suffix],
                       capture_output=True, text=True, timeout=90)
    out = []
    for line in p.stdout.splitlines():
        if line.startswith(':') or not line.strip():
            continue
        out.append(line.split('\t')[0])
    return out


HELP_FLAG = re.compile(r'^\s+(?:-\w, )?--([a-zA-Z0-9][a-zA-Z0-9-]*)')


def cobra_flags(binary, path):
    """Read flags off the help page.

    Not off __complete: when a command has required flags still unset, cobra
    returns ONLY those, so completion silently hides every optional flag.
    """
    p = subprocess.run([binary] + list(path) + ['--help'],
                       capture_output=True, text=True, timeout=90)
    flags, inside = set(), False
    for line in p.stdout.splitlines():
        if line.rstrip().endswith('Flags:'):
            inside = True
            continue
        if line and not line.startswith(' '):
            inside = False
        if inside:
            m = HELP_FLAG.match(line)
            if m:
                flags.add(m.group(1))
    return sorted(flags)


def from_cobra(binary, path=(), tree=None, depth=0):
    tree = {} if tree is None else tree
    subs = [s for s in complete(binary, list(path), '') if not s.startswith('-')]
    flags = cobra_flags(binary, path)
    tree[' '.join(path) or '(root)'] = {'subs': sorted(subs), 'flags': sorted(flags)}
    if depth < 4:
        for sub in subs:
            from_cobra(binary, tuple(path) + (sub,), tree, depth + 1)
    return tree


if __name__ == '__main__':
    kind, target = sys.argv[1], sys.argv[2]
    result = from_bash(target) if kind == 'ts' else from_cobra(target)
    print(json.dumps(result, indent=1, sort_keys=True))
