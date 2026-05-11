#!/usr/bin/env python3

from __future__ import annotations

import pathlib
import struct
import sys

LC_CODE_SIGNATURE = 0x1D
MH_MAGIC_64 = 0xFEEDFACF
CSMAGIC_EMBEDDED_SIGNATURE = 0xFADE0CC0
DEFAULT_PATHS = (
    "build/appwrite-cli-darwin-x64",
    "build/appwrite-cli-darwin-arm64",
)


def has_valid_code_signature(path: pathlib.Path) -> bool:
    data = path.read_bytes()
    if len(data) < 32:
        raise ValueError("file is too small to be a Mach-O binary")

    header = struct.unpack_from("<8I", data, 0)
    magic = header[0]
    ncmds = header[4]
    if magic != MH_MAGIC_64:
        raise ValueError(f"unexpected Mach-O magic {magic:#x}")

    offset = 32
    for _ in range(ncmds):
        cmd, cmdsize = struct.unpack_from("<II", data, offset)
        if cmd == LC_CODE_SIGNATURE:
            data_offset, data_size = struct.unpack_from("<II", data, offset + 8)
            if data_offset + data_size > len(data):
                raise ValueError("code signature blob extends past end of file")
            if data_size < 8:
                raise ValueError("code signature blob is too small")

            blob_magic, blob_length = struct.unpack_from(">II", data, data_offset)
            if blob_magic != CSMAGIC_EMBEDDED_SIGNATURE:
                raise ValueError(f"unexpected code signature magic {blob_magic:#x}")
            if blob_length > data_size:
                raise ValueError("code signature blob length exceeds Mach-O load command size")
            if data_offset + blob_length > len(data):
                raise ValueError("code signature blob length extends past end of file")

            return True
        offset += cmdsize

    return False


def main(argv: list[str]) -> int:
    failures: list[str] = []

    for relative in argv or list(DEFAULT_PATHS):
        path = pathlib.Path(relative)
        if not path.exists():
            failures.append(f"{relative}: file is missing")
            continue

        try:
            if not has_valid_code_signature(path):
                failures.append(f"{relative}: missing LC_CODE_SIGNATURE")
        except Exception as error:
            failures.append(f"{relative}: {error}")

    if failures:
        for failure in failures:
            print(failure, file=sys.stderr)
        return 1

    return 0


if __name__ == "__main__":
    raise SystemExit(main(sys.argv[1:]))
