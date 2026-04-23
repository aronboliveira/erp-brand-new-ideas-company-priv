#!/usr/bin/env python3
"""
Audit web.php route definitions for raw strings that should use VW:: constants.

Scans routes/web.php for:
1. ->name('raw.string') that should use VW:: or other constants
2. Raw URI strings that could use VW:: path constants
3. Raw middleware strings that should use MWC:: constants

Reports findings and suggests constant names.
"""

import re
import sys
from pathlib import Path

LARAVEL_ROOT = Path(__file__).resolve().parents[3] / "laravel"
WEB_PHP = LARAVEL_ROOT / "routes" / "web.php"


def audit_raw_route_names(content: str, lines: list[str]) -> list[dict]:
    """Find ->name('raw.string') calls that don't use VW:: constants."""
    findings = []
    for i, line in enumerate(lines, 1):
        stripped = line.strip()
        if stripped.startswith("//") or stripped.startswith("#"):
            continue
        matches = re.findall(r"->name\(\s*'([^']+)'\s*\)", line)
        for raw_name in matches:
            if " " not in raw_name and (
                "." in raw_name or "-" in raw_name or "_" in raw_name
            ):
                findings.append({
                    "line": i,
                    "type": "raw_route_name",
                    "value": raw_name,
                    "context": stripped[:120],
                })
    return findings


def audit_raw_middleware(content: str, lines: list[str]) -> list[dict]:
    """Find raw middleware strings that should use MWC:: constants."""
    findings = []
    known_mwc = {"auth", "web", "guest", "verified", "xss", "throttle"}
    for i, line in enumerate(lines, 1):
        stripped = line.strip()
        if stripped.startswith("//"):
            continue
        mw_matches = re.findall(r"middleware\(\s*\[([^\]]+)\]", line)
        for mw_content in mw_matches:
            raw_strings = re.findall(r"'([^']+)'", mw_content)
            for rs in raw_strings:
                if rs.lower() in known_mwc or ":" in rs:
                    findings.append({
                        "line": i,
                        "type": "raw_middleware",
                        "value": rs,
                        "context": stripped[:120],
                    })
    return findings


def suggest_constant_name(raw_name: str) -> str:
    """Suggest a VW:: constant name for a raw route name."""
    cleaned = raw_name.replace(".", "_").replace("-", "_").upper()
    parts = cleaned.split("_")
    if len(parts) > 3:
        abbrev = "".join(p[:3] for p in parts[:3]) + "_" + "_".join(parts[3:])
        return abbrev
    return cleaned


def main():
    if not WEB_PHP.exists():
        print(f"ERROR: {WEB_PHP} not found")
        sys.exit(1)

    content = WEB_PHP.read_text()
    lines = content.splitlines()

    name_count_vw = len(re.findall(r"->name\(\s*VW::", content))
    name_count_raw = len(re.findall(r"->name\(\s*'", content))

    print(f"Route names using VW:: constants: {name_count_vw}")
    print(f"Route names using raw strings:    {name_count_raw}")
    print(f"Constants adoption rate:          {name_count_vw}/{name_count_vw + name_count_raw} ({100*name_count_vw//(name_count_vw+name_count_raw)}%)")

    raw_names = audit_raw_route_names(content, lines)
    raw_mw = audit_raw_middleware(content, lines)

    if raw_names:
        active = [f for f in raw_names if not any(
            x in f["context"] for x in ("//", "# ")
        )]
        print(f"\nActive raw route names needing VW:: constants ({len(active)}):")
        for f in active:
            suggest = suggest_constant_name(f["value"])
            print(f"  L{f['line']:4d}: name('{f['value']}') -> VW::{suggest}")

    if raw_mw:
        print(f"\nRaw middleware strings ({len(raw_mw)}):")
        for f in raw_mw[:20]:
            print(f"  L{f['line']:4d}: '{f['value']}'")

    print(f"\nTotal findings: {len(raw_names)} raw route names, {len(raw_mw)} raw middleware")
    return 0 if not raw_names else 1


if __name__ == "__main__":
    sys.exit(main())
