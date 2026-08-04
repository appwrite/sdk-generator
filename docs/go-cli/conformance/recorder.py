#!/usr/bin/env python3
"""Record every HTTP request either CLI sends, and answer plausibly.

The response only has to be parseable -- what is under test is the request:
method, path, query, headers and body. Each request is appended to a JSONL
file named by the X-Conformance-Case header the driver sets via the endpoint
path prefix.
"""
import json
import os
import sys
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer

OUT = os.environ.get('RECORD_TO', 'requests.jsonl')

# Headers that legitimately differ between two clients and say nothing about
# whether the CLIs agree on the request.
IGNORED = {
    'host', 'connection', 'content-length', 'accept-encoding', 'user-agent',
    'x-sdk-name', 'x-sdk-version', 'x-sdk-platform', 'x-sdk-language',
    'x-appwrite-response-format', 'accept', 'sec-fetch-mode', 'accept-language',
}

BODY = {
    '$id': 'stub', '$createdAt': '2026-01-01T00:00:00.000+00:00',
    'total': 0, 'name': 'stub', 'status': 'completed', 'secret': 'stub',
    'jwt': 'stub', 'chunksTotal': 1, 'chunksUploaded': 1,
    # `client --endpoint` in the TypeScript refuses to save an endpoint whose
    # /health/version does not answer with a version.
    'version': '1.9.0',
}
# Every list endpoint names its array differently; supplying them all keeps the
# renderers from crashing before the next request goes out.
for key in ('users', 'teams', 'files', 'buckets', 'functions', 'sites',
            'databases', 'collections', 'documents', 'tables', 'rows',
            'columns', 'indexes', 'attributes', 'deployments', 'executions',
            'variables', 'memberships', 'sessions', 'logs', 'projects',
            'webhooks', 'keys', 'platforms', 'domains', 'rules', 'topics',
            'subscribers', 'messages', 'providers', 'targets', 'identities',
            'migrations', 'notifications', 'tokens', 'policies', 'archives',
            'restorations', 'presences', 'installations', 'repositories',
            'branches', 'activities', 'events', 'continents', 'countries',
            'currencies', 'languages', 'phones', 'locale', 'factors',
            'recoveryCodes', 'organizations', 'consents', 'scopes', 'apps'):
    BODY[key] = []


class Handler(BaseHTTPRequestHandler):
    def log_message(self, *args):
        pass

    def record(self):
        length = int(self.headers.get('content-length') or 0)
        raw = self.rfile.read(length) if length else b''

        path, _, query = self.path.partition('?')
        # The driver encodes the case name as the first path segment so
        # concurrent cases cannot interleave.
        parts = path.lstrip('/').split('/', 1)
        case, rest = parts[0], '/' + (parts[1] if len(parts) > 1 else '')

        try:
            body = json.loads(raw)
        except Exception:
            body = raw.decode('utf-8', 'replace')[:400] if raw else None

        entry = {
            'case': case,
            'method': self.command,
            'path': rest,
            'query': query,
            'headers': {k.lower(): v for k, v in self.headers.items()
                        if k.lower() not in IGNORED},
            'body': body,
        }
        with open(OUT, 'a') as handle:
            handle.write(json.dumps(entry, sort_keys=True) + '\n')

        payload = json.dumps(BODY).encode()
        self.send_response(200)
        self.send_header('content-type', 'application/json')
        self.send_header('content-length', str(len(payload)))
        self.end_headers()
        self.wfile.write(payload)

    do_GET = do_POST = do_PUT = do_PATCH = do_DELETE = record


if __name__ == '__main__':
    ThreadingHTTPServer(('127.0.0.1', int(sys.argv[1])), Handler).serve_forever()
