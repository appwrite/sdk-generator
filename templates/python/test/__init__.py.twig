import httpx2

# The SDK talks HTTP through httpx2, while respx patches `httpx`. Point `import
# httpx` at httpx2 so respx mocks the transport the SDK actually uses, and so
# `httpx2.Response` satisfies respx's `httpx.Response` check.
#
# This has to run before respx is imported, and it is idempotent, so every
# package that pulls respx in repeats it: this module covers discovery from the
# repository root (and pytest), `test/services/__init__.py` covers discovery
# from inside the test directory.
httpx2.alias_httpx()
