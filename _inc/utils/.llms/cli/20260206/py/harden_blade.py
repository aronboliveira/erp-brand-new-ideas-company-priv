#!/usr/bin/env python3
r"""Blade view hardening script.

For each .blade.php file inside DIRS:
1. Remove PHP comments inside @php/@endphp blocks
2. Normalize newlines (max 1 consecutive blank)
3. Remove trailing whitespace
4. Convert top-of-block simple init `=` to `??=`
5. Wrap non-trivial @php blocks in try/catch with \Log::error
"""

import os
import re
import sys

BASE = "/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel"

DIRS = [
    "resources/views",  
]

SIMPLE_DEFAULTS = re.compile(
    r"^\s*\$(\w+)\s*=\s*("
    r"null|''|\"\"|\[\]|0|0\.0|false|true|'[^']*'|\"[^\"]*\""
    r"|collect\(\)|Auth::user\(\)"
    r"|request\(\)"
    r")\s*;\s*$"
)

ALREADY_NULLCOAL = re.compile(r'^\s*\$\w+\s*\?\?=')
HAS_TRY = re.compile(r'^\s*try\s*\{')


def remove_php_comments(code: str) -> str:
    """Remove PHP comments, preserving strings."""
    result = []
    i = 0
    n = len(code)
    while i < n:
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
        if code[i:i+2] == '/*':
            end = code.find('*/', i + 2)
            if end == -1:
                i = n
            else:
                i = end + 2
                if i < n and code[i] == '\n':
                    i += 1
            continue
        if code[i:i+2] == '//':
            rest = code[i:code.find('\n', i) if code.find('\n', i) != -1 else n]
            if re.search(r'@(phpstan|codeCoverage|noinspection|psalm)', rest):
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


def normalize_newlines(code: str) -> str:
    while '\n\n\n' in code:
        code = code.replace('\n\n\n', '\n\n')
    return code


def remove_trailing_ws(code: str) -> str:
    return '\n'.join(line.rstrip() for line in code.split('\n'))


def detect_view_path(filepath: str) -> str:
    """Extract a short view identifier for log messages."""
    rel = os.path.relpath(filepath, BASE)
    rel = rel.replace('resources/views/', '').replace('.blade.php', '')
    return rel


def convert_inits_to_nullcoal(block_lines: list[str]) -> list[str]:
    """Convert top-of-block simple initializations from = to ??=."""
    result = []
    past_inits = False
    for line in block_lines:
        stripped = line.strip()
        if not stripped:
            result.append(line)
            continue
        if not past_inits and SIMPLE_DEFAULTS.match(stripped):
            if not ALREADY_NULLCOAL.match(stripped):
                new_line = line.replace(' = ', ' ??= ', 1)
                result.append(new_line)
            else:
                result.append(line)
        else:
            past_inits = True
            result.append(line)
    return result


def block_has_try(lines: list[str]) -> bool:
    for line in lines:
        if HAS_TRY.match(line):
            return True
    return False


def block_is_trivial(lines: list[str]) -> bool:
    """Block is trivial if it has <= 2 non-blank lines."""
    non_blank = [l for l in lines if l.strip()]
    return len(non_blank) <= 2


def detect_indent(lines: list[str]) -> str:
    """Detect the common indent used in the block."""
    for line in lines:
        if line.strip():
            leading = len(line) - len(line.lstrip())
            return line[:leading]
    return '\t'


def wrap_block_in_try_catch(block_lines: list[str], view_path: str) -> list[str]:
    """Wrap the procedure part of a @php block in try/catch.

    Keeps ??= inits before the try, wraps the rest.
    """
    if block_has_try(block_lines):
        return block_lines
    if block_is_trivial(block_lines):
        return block_lines

    indent = detect_indent(block_lines)
    if not indent:
        indent = '\t'
    inner = indent + '\t' if indent == '\t' else indent + '    '

    init_lines = []
    body_lines = []
    past_inits = False

    for line in block_lines:
        stripped = line.strip()
        if not stripped and not past_inits:
            init_lines.append(line)
            continue
        if not past_inits and (ALREADY_NULLCOAL.match(stripped) or
                               SIMPLE_DEFAULTS.match(stripped)):
            init_lines.append(line)
        else:
            past_inits = True
            body_lines.append(line)

    if not body_lines or all(not l.strip() for l in body_lines):
        return block_lines

    reindented = []
    for bl in body_lines:
        if bl.strip() == '':
            reindented.append('')
        elif bl.startswith(indent):
            reindented.append(inner + bl[len(indent):])
        else:
            reindented.append(inner + bl)

    log_msg = (
        f"'{view_path} — ' . "
        "get_class($e) . ': ' . $e->getMessage()"
    )

    result = init_lines[:]
    result.append(f"{indent}try {{")
    result.extend(reindented)
    result.append(f"{indent}}} catch (\\Throwable $e) {{")
    result.append(
        f"{inner}\\Log::error({log_msg}, "
        "['file' => $e->getFile(), 'line' => $e->getLine()]);"
    )
    result.append(f"{indent}}}")

    return result


def process_php_blocks(content: str, view_path: str) -> str:
    """Find all @php/@endphp blocks and harden them."""
    pattern = re.compile(
        r'(@php\b)(.*?)(@endphp)',
        re.DOTALL
    )

    def harden_block(m):
        opening = m.group(1)
        body = m.group(2)
        closing = m.group(3)

        if not body.strip():
            return m.group(0)

        body = remove_php_comments(body)

        lines = body.split('\n')
        if lines and lines[0] == '':
            lines = lines[1:]
        if lines and lines[-1].strip() == '':
            lines = lines[:-1]

        lines = convert_inits_to_nullcoal(lines)
        lines = wrap_block_in_try_catch(lines, view_path)

        result_body = '\n'.join(lines)
        return f"{opening}\n{result_body}\n{closing}"

    return pattern.sub(harden_block, content)


def process_file(filepath: str) -> bool:
    """Process a single Blade file. Returns True if modified."""
    with open(filepath, 'r', encoding='utf-8') as f:
        original = f.read()

    view_path = detect_view_path(filepath)
    content = original

    content = process_php_blocks(content, view_path)
    content = normalize_newlines(content)
    content = remove_trailing_ws(content)
    content = content.rstrip('\n') + '\n'

    if content != original:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
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
            dirs.sort()
            for fname in sorted(files):
                if not fname.endswith('.blade.php'):
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
