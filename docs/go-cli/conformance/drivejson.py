#!/usr/bin/env python3
"""Diff --json stdout between the two CLIs for every service command.

PLAN.md invariant 4: `--json` is scripted against, so the bytes are the
contract. The recorder answers both CLIs with the same stub body, so any
difference in what they print is a rendering difference, not a data one.
"""
import json
import subprocess
import sys

import drive


def stdout_of(binary, args, env, cwd):
    p = subprocess.run([binary] + args + ['--json'], capture_output=True,
                       text=True, env=env, cwd=cwd, timeout=120)
    return p.returncode, p.stdout


if __name__ == '__main__':
    only = sys.argv[1] if len(sys.argv) > 1 else None
    report, total = [], 0

    for path in drive.commands():
        joined = ' '.join(path)
        if only and not joined.startswith(only):
            continue
        total += 1
        args = drive.argv_for(path)

        go_code, go_out = stdout_of(drive.GO, args, drive.GO_ENV, drive.GO_CWD)
        ts_code, ts_out = stdout_of(drive.TS, args, drive.TS_ENV, drive.TS_CWD)

        if go_out != ts_out:
            report.append({'command': joined, 'go': go_out[:600],
                           'ts': ts_out[:600],
                           'exits': [go_code, ts_code]})
        if total % 50 == 0:
            print(f'  ... {total} commands, {len(report)} differing',
                  file=sys.stderr, flush=True)

    print(json.dumps({'total': total, 'differing': len(report),
                      'cases': report}, indent=1))
