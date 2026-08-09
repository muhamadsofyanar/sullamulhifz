#!/usr/bin/env python3
"""@phase 4.2-4.4 Release traceability gate."""

from __future__ import annotations

import json
import pathlib
import sys


ROOT = pathlib.Path(__file__).resolve().parents[1]
MANIFEST = ROOT / "PHASE-MANIFEST.json"


def main() -> int:
    data = json.loads(MANIFEST.read_text(encoding="utf-8"))
    known = set(data["phases"])
    errors: list[str] = []
    seen: set[str] = set()

    for entry in data["files"]:
        relative = entry["path"]
        path = ROOT / relative
        if relative in seen:
            errors.append(f"duplicate manifest entry: {relative}")
        seen.add(relative)
        if not path.is_file():
            errors.append(f"missing file: {relative}")
            continue
        content = path.read_text(encoding="utf-8", errors="replace")
        for phase in entry["phases"]:
            if phase not in known:
                errors.append(f"unknown phase {phase}: {relative}")
            if f"@phase {phase}" not in content:
                errors.append(f"missing @phase {phase}: {relative}")

    if errors:
        print("Phase manifest validation failed:")
        for error in errors:
            print(f"- {error}")
        return 1

    print(f"Phase manifest valid: {len(seen)} tracked files across {len(known)} phases.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
