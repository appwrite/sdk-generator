#!/usr/bin/env python3
"""Run every generated service command on both CLIs and diff what they send.

For each command the driver fills its required flags with type-appropriate
placeholders, runs the Go binary and then the TypeScript one against the
recording server, and compares the resulting request lines. A command whose
two CLIs disagree on method, path, query, body or headers -- or on whether the
invocation is even accepted -- is reported.
"""
import json
import os
import re
import subprocess
import sys

S = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.dirname(S)
GO = os.path.join(S, 'appwrite-go')
TS = os.path.join(S, 'appwrite-ts')
RECORD = os.path.join(ROOT, 'rec', 'req.jsonl')

GO_ENV = dict(os.environ, HOME=os.path.join(ROOT, 'rec', 'hgo'),
              APPWRITE_PROJECT_ID='testproj')
TS_ENV = dict(os.environ, HOME=os.path.join(ROOT, 'rec', 'hts'))
GO_CWD = os.path.join(ROOT, 'rec', 'wgo')
TS_CWD = os.path.join(ROOT, 'rec', 'wts')

# Commands with local side effects or interactive flows; covered separately.
SKIP_ROOTS = {'client', 'init', 'login', 'logout', 'update', 'push', 'pull',
              'run', 'generate', 'types', 'completion', 'sessions', 'whoami',
              'help'}

# Headers each client legitimately stamps differently are dropped at the
# recorder; what remains is compared verbatim.
UPLOAD = os.path.join(ROOT, 'rec', 'upload.txt')


def flag_specs(path):
    """{flag: type} for one command, read off the Go help page."""
    p = subprocess.run([GO] + path + ['--help'], capture_output=True,
                       text=True, env=GO_ENV, cwd=GO_CWD, timeout=90)
    specs, inside = {}, False
    for line in p.stdout.splitlines():
        if line.rstrip().endswith('Flags:'):
            inside = line.strip() == 'Flags:'
            continue
        if line and not line.startswith(' '):
            inside = False
        if not inside:
            continue
        m = re.match(r'^\s+(?:-\w, )?--([a-zA-Z0-9][a-zA-Z0-9-]*)\s*(\S+)?', line)
        if m and m.group(1) != 'help':
            specs[m.group(1)] = (m.group(2) or '').strip()
    return specs


def required(path):
    """Flags cobra reports as required-and-unset."""
    p = subprocess.run([GO, '__complete'] + path + ['-'], capture_output=True,
                       text=True, env=GO_ENV, cwd=GO_CWD, timeout=90)
    flags = [line.split('\t')[0][2:] for line in p.stdout.splitlines()
             if line.startswith('--')]
    # With no required flags outstanding cobra falls back to listing every
    # flag, `--help` among them. That listing is not a requirement set.
    return [] if 'help' in flags else flags


def value_for(flag, kind):
    # Only a genuine upload flag takes a path. `--file-id` is an id, and
    # feeding it a path made every id look like an escaping bug.
    if flag in ('file', 'code', 'path') or flag.endswith('-file'):
        return UPLOAD
    if kind in ('int', 'int64', 'float', 'float64'):
        return '1'
    if kind in ('', 'bool'):
        return None                      # boolean: pass the flag bare
    if kind == 'stringToString':
        return 'k=v'
    return 'stub'


def argv_for(path):
    specs = flag_specs(path)
    args = list(path)
    for flag in required(path):
        value = value_for(flag, specs.get(flag, 'string'))
        args.append('--' + flag)
        if value is not None:
            args.append(value)
    return args


def capture(binary, args, env, cwd):
    open(RECORD, 'w').close()
    p = subprocess.run([binary] + args, capture_output=True, text=True,
                       env=env, cwd=cwd, timeout=120)
    lines = []
    if not os.path.exists(RECORD):
        return p.returncode, lines
    with open(RECORD) as handle:
        for line in handle:
            entry = json.loads(line)
            entry.pop('case', None)
            lines.append(entry)
    return p.returncode, lines


def commands():
    tree = json.load(open(os.path.join(S, 'go-tree.json')))
    for path in sorted(tree):
        if path == '(root)':
            continue
        parts = path.split()
        if parts[0] in SKIP_ROOTS or tree[path]['subs']:
            continue
        yield parts


def normalise(entries):
    for entry in entries:
        # Both clients send the same locale; only the TypeScript announces it.
        entry['headers'].pop('x-appwrite-locale', None)
        entry['headers'].pop('content-type', None)
    return entries


if __name__ == '__main__':
    only = sys.argv[1] if len(sys.argv) > 1 else None
    report = []
    total = 0
    for path in commands():
        joined = ' '.join(path)
        if only and not joined.startswith(only):
            continue
        total += 1
        args = argv_for(path)
        go_code, go_reqs = capture(GO, args, GO_ENV, GO_CWD)
        ts_code, ts_reqs = capture(TS, args, TS_ENV, TS_CWD)
        go_reqs, ts_reqs = normalise(go_reqs), normalise(ts_reqs)

        if go_reqs != ts_reqs or bool(go_code) != bool(ts_code):
            report.append({
                'command': joined,
                'argv': args[len(path):],
                'go': {'exit': go_code, 'requests': go_reqs},
                'ts': {'exit': ts_code, 'requests': ts_reqs},
            })
        if total % 50 == 0:
            print(f'  ... {total} commands, {len(report)} differing',
                  file=sys.stderr, flush=True)

    print(json.dumps({'total': total, 'differing': len(report),
                      'cases': report}, indent=1))
