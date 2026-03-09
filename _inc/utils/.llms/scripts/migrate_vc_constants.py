#!/usr/bin/env python3
"""
Migrate raw multi-word CSS class strings in Blade views to VC:: constants.

Strategy:
1. Load the constants map (name=value, sorted by value length DESC)
2. For each blade file, find class="..." attributes
3. Replace EXACT full-value matches first (highest priority)
4. Then try longest-first substring matches (token-boundary aware)
5. Handle multiple replacements per attribute
6. Only replace multi-word values (containing separators)
7. Skip files in vendor/ directories
8. Never touch attributes that already use VC:: / ViewClassNamesConstants::
"""

import os
import re
import sys
from pathlib import Path

LARAVEL_ROOT = Path(__file__).resolve().parents[3] / "laravel"
VIEWS_DIR = LARAVEL_ROOT / "resources" / "views"
CONSTANTS_MAP_FILE = "/tmp/vc_constants_map.txt"


def load_constants_map(path: str) -> list[tuple[str, str]]:
    """Load constants sorted by value length DESC (longest match first)."""
    pairs = []
    with open(path, "r") as f:
        for line in f:
            line = line.strip()
            if not line or "=" not in line:
                continue
            name, value = line.split("=", 1)
            name = name.strip()
            value = value.strip()
            if len(value) > 2 and re.search(r"[ _-]", value):
                pairs.append((name, value))
    pairs.sort(key=lambda x: len(x[1]), reverse=True)
    return pairs


def build_exact_map(constants: list[tuple[str, str]]) -> dict[str, str]:
    """Build a dict of value → constant name for exact full-match lookups."""
    m = {}
    for name, value in constants:
        if value not in m:
            m[value] = name
    return m


def find_blade_files(views_dir: Path) -> list[Path]:
    """Find all blade files, excluding vendor."""
    files = []
    for f in views_dir.rglob("*.blade.php"):
        rel = str(f.relative_to(views_dir))
        if rel.startswith("vendor/"):
            continue
        files.append(f)
    files.sort()
    return files


def has_dynamic_content(s: str) -> bool:
    """Check if value contains Blade expressions {{ }}, {!! !!}, or PHP tags."""
    return "{{" in s or "{!!" in s or "<?php" in s


def already_uses_constant(attr_val: str) -> bool:
    """Check if the value already uses {{ VC:: }} or similar."""
    return "VC::" in attr_val or "ViewClassNamesConstants::" in attr_val


def is_at_token_boundary(full: str, start: int, end: int) -> bool:
    """
    Check if the match at full[start:end] is at CSS class token boundaries.
    CSS classes are space-separated, so boundaries are start/end of string or spaces.
    """
    if start > 0 and full[start - 1] != " ":
        return False
    if end < len(full) and full[end] != " ":
        return False
    return True


def replace_in_value(val: str, constants: list[tuple[str, str]],
                     exact_map: dict[str, str]) -> tuple[str, int]:
    """
    Replace raw multi-word class strings with VC:: constants.
    Returns (new_value, replacement_count).

    Approach:
    1. Try exact full match
    2. Iteratively find longest substring match at token boundaries
    """
    if already_uses_constant(val):
        return val, 0

    stripped = val.strip()
    if not stripped:
        return val, 0

    if stripped in exact_map:
        return "{{ VC::" + exact_map[stripped] + " }}", 1

    count = 0
    working = val

    for const_name, const_value in constants:
        if const_value not in working:
            continue

        idx = working.find(const_value)
        while idx != -1:
            end = idx + len(const_value)
            if is_at_token_boundary(working, idx, end):
                repl = "{{ VC::" + const_name + " }}"
                working = working[:idx] + repl + working[end:]
                count += 1
                break
            idx = working.find(const_value, idx + 1)

    if count > 0:
        parts = working.split()
        working = " ".join(parts)

    return working, count


def replace_class_attrs(content: str, constants: list[tuple[str, str]],
                        exact_map: dict[str, str]) -> tuple[str, int]:
    """
    Find class="..." attributes and replace raw multi-word values with VC:: constants.
    Returns (new_content, replacement_count).
    """
    total_count = 0

    def replace_match(m: re.Match) -> str:
        nonlocal total_count
        prefix = m.group(1)
        val = m.group(2)
        quote = m.group(3)

        new_val, count = replace_in_value(val, constants, exact_map)
        if count > 0:
            total_count += count
            return prefix + new_val + quote
        return m.group(0)

    pattern_dq = r'(class=")([^"]*?)(")'
    pattern_sq = r"(class=')([^']*?)(')"
    new_content = re.sub(pattern_dq, replace_match, content)
    new_content = re.sub(pattern_sq, replace_match, new_content)
    return new_content, total_count


def migrate_file(blade_file: Path, constants: list[tuple[str, str]],
                 exact_map: dict[str, str], dry_run: bool = False) -> int:
    """Migrate a single blade file. Returns number of replacements."""
    content = blade_file.read_text(encoding="utf-8", errors="replace")
    new_content, count = replace_class_attrs(content, constants, exact_map)

    if count > 0 and not dry_run:
        blade_file.write_text(new_content, encoding="utf-8")

    return count


def main():
    dry_run = "--dry-run" in sys.argv
    verbose = "--verbose" in sys.argv or "-v" in sys.argv

    if not os.path.exists(CONSTANTS_MAP_FILE):
        print(f"ERROR: Constants map not found at {CONSTANTS_MAP_FILE}")
        sys.exit(1)

    constants = load_constants_map(CONSTANTS_MAP_FILE)
    exact_map = build_exact_map(constants)
    print(f"Loaded {len(constants)} multi-word constants")

    blade_files = find_blade_files(VIEWS_DIR)
    print(f"Found {len(blade_files)} blade files (excluding vendor)")

    total_replacements = 0
    modified_files = 0

    for f in blade_files:
        count = migrate_file(f, constants, exact_map, dry_run=dry_run)
        if count > 0:
            modified_files += 1
            total_replacements += count
            if verbose:
                rel = f.relative_to(VIEWS_DIR)
                print(f"  {rel}: {count} replacements")

    action = "Would modify" if dry_run else "Modified"
    print(f"\n{action} {modified_files} files with {total_replacements} total replacements")
    if dry_run:
        print("(dry run — no files were changed)")


if __name__ == "__main__":
    main()
