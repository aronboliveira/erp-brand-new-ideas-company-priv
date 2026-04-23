#!/usr/bin/env python3
r"""
Model hardening script - safe mechanical transformations:
1. Sort imports alphabetically with {} spreading
2. Remove all PHP comments (// /* */ /** */) except inline suppression directives
3. Normalize newlines (max 1 consecutive blank)
4. Ensure backslash-prefixed Error / Exception (not imported)
5. Remove trailing whitespace

Usage: Update DIRS list below, then run: python3 harden_models.py
"""

import os
import re
import sys
from pathlib import Path

BASE = "/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel"

DIRS = [
    # Update this list before running
]


def remove_comments(code: str) -> str:
    """Remove PHP comments carefully, preserving strings and regex."""
    result = []
    i = 0
    n = len(code)
    while i < n:
        # Single-quoted string
        if code[i] == "'":
            j = i + 1
            while j < n:
                if code[j] == "\\" and j + 1 < n:
                    j += 2
                    continue
                if code[j] == "'":
                    j += 1
                    break
                j += 1
            result.append(code[i:j])
            i = j
            continue
        # Double-quoted string
        if code[i] == '"':
            j = i + 1
            while j < n:
                if code[j] == "\\" and j + 1 < n:
                    j += 2
                    continue
                if code[j] == '"':
                    j += 1
                    break
                j += 1
            result.append(code[i:j])
            i = j
            continue
        # Multi-line / PHPDoc comment
        if code[i:i+2] == '/*':
            end = code.find('*/', i + 2)
            if end == -1:
                i = n
            else:
                line_start = code.rfind('\n', 0, i)
                before = code[line_start+1:i].strip() if line_start >= 0 else code[:i].strip()
                if before:
                    i = end + 2
                else:
                    i = end + 2
                    if i < n and code[i] == '\n':
                        i += 1
            continue
        # Single-line comment
        if code[i:i+2] == '//':
            rest_of_line = code[i:code.find('\n', i) if code.find('\n', i) != -1 else n]
            if re.search(r'@(phpstan|codeCoverage|noinspection|psalm)', rest_of_line):
                eol = code.find('\n', i)
                if eol == -1:
                    result.append(code[i:])
                    i = n
                else:
                    result.append(code[i:eol])
                    i = eol
                continue
            line_start = code.rfind('\n', 0, i)
            before = code[line_start+1:i].strip() if line_start >= 0 else code[:i].strip()
            eol = code.find('\n', i)
            if eol == -1:
                eol = n
            if before:
                i = eol
            else:
                i = eol
                if i < n and code[i] == '\n':
                    i += 1
            continue
        # Hash comment
        if code[i] == '#' and (i == 0 or code[i-1] in ('\n', ' ', '\t')):
            eol = code.find('\n', i)
            if eol == -1:
                i = n
            else:
                i = eol + 1
            continue
        result.append(code[i])
        i += 1
    return ''.join(result)


def sort_imports(code: str) -> str:
    """Sort use statements alphabetically, grouping by top-level namespace."""
    lines = code.split('\n')
    use_start = -1
    use_end = -1
    namespace_line = -1

    for idx, line in enumerate(lines):
        stripped = line.strip()
        if stripped.startswith('namespace '):
            namespace_line = idx
        if stripped.startswith('use ') and stripped.endswith(';') and namespace_line >= 0:
            if use_start == -1:
                use_start = idx
            use_end = idx
        elif use_start >= 0 and use_end >= 0 and stripped and not stripped.startswith('use '):
            break

    if use_start == -1 or use_end == -1:
        return code

    use_lines = []
    for idx in range(use_start, use_end + 1):
        stripped = lines[idx].strip()
        if stripped.startswith('use ') and stripped.endswith(';'):
            use_lines.append(stripped)

    if not use_lines:
        return code

    parsed = []
    for u in use_lines:
        body = u[4:-1].strip()
        parsed.append(body)

    expanded = []
    for body in parsed:
        m = re.match(r'^(.+?)\\{(.+)}$', body)
        if m:
            prefix = m.group(1)
            items = [x.strip() for x in m.group(2).split(',')]
            for item in items:
                expanded.append(f"{prefix}\\{item}")
        else:
            expanded.append(body)

    filtered = []
    for imp in expanded:
        clean = imp.strip()
        if clean in ('Error', 'Exception', 'RuntimeException',
                      'InvalidArgumentException', 'LogicException',
                      'BadMethodCallException', 'OutOfBoundsException',
                      'OverflowException', 'UnderflowException',
                      'RangeException', 'LengthException',
                      'DomainException', 'UnexpectedValueException'):
            continue
        filtered.append(clean)

    expanded = filtered
    expanded = list(dict.fromkeys(expanded))
    expanded.sort(key=lambda x: x.lower())

    groups = {}
    for imp in expanded:
        parts = imp.rsplit('\\', 1)
        if len(parts) == 2:
            prefix, item = parts
            groups.setdefault(prefix, []).append(item)
        else:
            groups.setdefault('', []).append(imp)

    final_uses = []
    for prefix in sorted(groups.keys(), key=lambda x: x.lower()):
        items = groups[prefix]
        if prefix == '':
            for item in sorted(items, key=lambda x: x.lower()):
                final_uses.append(f"use {item};")
        elif len(items) == 1:
            final_uses.append(f"use {prefix}\\{{{items[0]}}};")
        else:
            items.sort(key=lambda x: x.lower())
            single = f"use {prefix}\\{{{', '.join(items)}}};"
            if len(single) <= 100:
                final_uses.append(single)
            else:
                final_uses.append(f"use {prefix}\\{{")
                for j, item in enumerate(items):
                    comma = ',' if j < len(items) - 1 else ''
                    final_uses.append(f"\t{item}{comma}")
                final_uses.append("};")

    new_lines = lines[:use_start] + final_uses + lines[use_end + 1:]
    return '\n'.join(new_lines)


def normalize_newlines(code: str) -> str:
    while '\n\n\n' in code:
        code = code.replace('\n\n\n', '\n\n')
    return code


def remove_trailing_whitespace(code: str) -> str:
    lines = code.split('\n')
    return '\n'.join(line.rstrip() for line in lines)


def ensure_backslash_exceptions(code: str) -> str:
    bare_exceptions = [
        'Exception', 'Error', 'TypeError', 'ValueError',
        'RuntimeException', 'InvalidArgumentException',
        'LogicException', 'BadMethodCallException',
        'JsonException', 'Throwable',
    ]

    def fix_multi_catch(m):
        types_str = m.group(1)
        fixed_types = []
        for t in re.split(r'\s*\|\s*', types_str):
            t = t.strip()
            if t in bare_exceptions and not t.startswith('\\'):
                t = '\\' + t
            fixed_types.append(t)
        return 'catch (' + ' | '.join(fixed_types)

    code = re.sub(r'catch\s*\(([^)]+)', fix_multi_catch, code)

    for exc in bare_exceptions:
        pattern = r'throw\s+new\s+(?<!\\)' + re.escape(exc) + r'\b'
        replacement = 'throw new \\\\' + exc
        code = re.sub(pattern, replacement, code)

    for exc in bare_exceptions:
        code = code.replace('\\\\\\' + exc, '\\' + exc)
        code = code.replace('\\\\' + exc, '\\' + exc)

    return code


def process_file(filepath: str) -> bool:
    with open(filepath, 'r', encoding='utf-8') as f:
        original = f.read()

    code = original
    code = remove_comments(code)
    code = sort_imports(code)
    code = normalize_newlines(code)
    code = remove_trailing_whitespace(code)
    code = ensure_backslash_exceptions(code)
    code = code.rstrip('\n') + '\n'

    if code != original:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(code)
        return True
    return False


def main():
    modified = 0
    total = 0

    for d in DIRS:
        full_dir = os.path.join(BASE, d)
        if not os.path.isdir(full_dir):
            print(f"  SKIP (not found): {d}")
            continue

        for root, dirs, files in os.walk(full_dir):
            for fname in sorted(files):
                if not fname.endswith('.php'):
                    continue
                fpath = os.path.join(root, fname)
                total += 1
                try:
                    changed = process_file(fpath)
                    rel = os.path.relpath(fpath, BASE)
                    if changed:
                        modified += 1
                        print(f"  MODIFIED: {rel}")
                    else:
                        print(f"  OK:       {rel}")
                except Exception as e:
                    print(f"  ERROR:    {fpath}: {e}")

    print(f"\nDone. {modified}/{total} files modified.")


if __name__ == '__main__':
    main()
