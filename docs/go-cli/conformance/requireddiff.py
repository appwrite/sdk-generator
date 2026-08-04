#!/usr/bin/env python3
"""Compare which flags each CLI insists on.

The TypeScript side is read from the generated sources: commander marks a flag
required with `.requiredOption`, and the `.command(...)` above it names the
subcommand. The Go side comes from cobra, which reports exactly the required
flags still unset when completing a bare `-`.
"""
import json
import os
import re
import sys

import drive

CLI = os.path.join('/Users/chiragaggarwal/Desktop/appwrite/appwrite',
                   'sdk-generator/examples/cli/lib/commands/services')
COMMAND = re.compile(r'^\s*\.command\(`([^`]+)`\)')
REQUIRED = re.compile(r'^\s*\.requiredOption\(\s*`--([a-zA-Z0-9-]+)')
OPTION = re.compile(r'^\s*\.option\(\s*`?--([a-zA-Z0-9-]+)')


def ts_required():
    """{'service sub': {flags}} for every generated TypeScript command."""
    out = {}
    for name in sorted(os.listdir(CLI)):
        service = name[:-3]
        current = None
        for line in open(os.path.join(CLI, name)):
            m = COMMAND.match(line)
            if m:
                current = f'{service} {m.group(1)}'
                out.setdefault(current, set())
                continue
            if current is None:
                continue
            m = REQUIRED.match(line)
            if m:
                out[current].add(m.group(1))
            elif OPTION.match(line) or line.startswith('const '):
                pass
    return out


if __name__ == '__main__':
    ts = ts_required()
    rows = []
    for path in drive.commands():
        joined = ' '.join(path)
        if joined not in ts:
            continue
        go = set(drive.required(path))
        want = ts[joined]
        if go != want:
            rows.append({'command': joined,
                         'required_only_in_go': sorted(go - want),
                         'required_only_in_ts': sorted(want - go)})
    print(json.dumps({'compared': len(ts), 'differing': len(rows),
                      'rows': rows}, indent=1))
