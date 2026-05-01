#!/usr/bin/env python3
r"""Deep semantic hardening for PHP model files (v2).

Robust try/catch wrapping using brace-depth tracking that skips
strings and heredocs. Also ensures Log facade import.

Usage: Update DIRS list below, then run: python3 harden_deep.py
"""

import re
import os
import sys

BASE = "/workspace/erp/_inc/laravel"
DIRS = [
    # Update this list before running
]

SKIP_METHODS = {
    'booted', '__construct', '__destruct', '__toString', '__clone',
    '__debugInfo', 'toArray', 'jsonSerialize', 'newFactory',
    'newCollection', 'newEloquentBuilder', 'resolveRouteBinding',
    'getRouteKeyName',
}

RETURN_DEFAULTS = {
    'bool':       'false',
    'int':        '0',
    'float':      '0.0',
    'string':     "''",
    'array':      '[]',
    'void':       None,
    'self':       None,
    'static':     None,
    'Collection': 'collect()',
    'Carbon':     'null',
}

RELATIONSHIP_RETURNS = {
    'BelongsTo', 'HasMany', 'HasOne', 'MorphMany', 'MorphOne',
    'MorphTo', 'BelongsToMany', 'HasManyThrough', 'HasOneThrough',
}


def count_braces_in_line(line):
    opens = 0
    closes = 0
    in_single = False
    in_double = False
    escaped = False
    for ch in line:
        if escaped:
            escaped = False
            continue
        if ch == '\\':
            escaped = True
            continue
        if ch == "'" and not in_double:
            in_single = not in_single
            continue
        if ch == '"' and not in_single:
            in_double = not in_double
            continue
        if in_single or in_double:
            continue
        if ch == '{':
            opens += 1
        elif ch == '}':
            closes += 1
    return opens, closes


def find_methods(lines):
    methods = []
    i = 0
    n = len(lines)
    while i < n:
        m = re.match(
            r'^(\s*)(public|protected|private)\s+'
            r'(static\s+)?function\s+(\w+)\s*\(',
            lines[i]
        )
        if not m:
            i += 1
            continue

        indent = m.group(1)
        is_static = bool(m.group(3))
        method_name = m.group(4)

        sig_start = i
        depth = 0
        found_open_brace = False
        opening_brace_line = None
        j = i

        while j < n:
            opens, closes = count_braces_in_line(lines[j])
            depth += opens - closes
            if opens > 0 and not found_open_brace:
                found_open_brace = True
                opening_brace_line = j
            if found_open_brace and depth == 0:
                break
            j += 1

        if not found_open_brace or j >= n:
            i += 1
            continue

        closing_brace_line = j

        sig_text = ' '.join(lines[k].strip() for k in range(sig_start, opening_brace_line + 1))
        ret_match = re.search(r'\)\s*:\s*([?\w\\|]+)\s*\{', sig_text)
        return_type = ret_match.group(1).strip() if ret_match else None

        if return_type and return_type.startswith('?'):
            return_type_clean = return_type[1:]
            nullable = True
        else:
            return_type_clean = return_type
            nullable = return_type is None

        methods.append({
            'name': method_name,
            'sig_start': sig_start,
            'open_brace_line': opening_brace_line,
            'close_brace_line': closing_brace_line,
            'indent': indent,
            'is_static': is_static,
            'return_type': return_type,
            'return_type_clean': return_type_clean,
            'nullable': nullable,
        })

        i = closing_brace_line + 1

    return methods


def body_has_try_catch(lines, open_brace, close_brace):
    for j in range(open_brace + 1, close_brace):
        stripped = lines[j].strip()
        if stripped == '':
            continue
        return stripped.startswith('try') and ('{' in stripped or
            (j + 1 < close_brace and '{' in lines[j + 1].strip()))
    return True


def is_simple_delegation(lines, open_brace, close_brace):
    body = []
    for j in range(open_brace + 1, close_brace):
        stripped = lines[j].strip()
        if stripped:
            body.append(stripped)
    if len(body) <= 2:
        return True
    return False


def is_relationship(lines, open_brace, close_brace):
    body_text = ' '.join(lines[j].strip() for j in range(open_brace + 1, close_brace))
    return bool(re.search(
        r'return\s+\$this->(belongsTo|hasMany|hasOne|morphMany|morphOne|'
        r'morphTo|belongsToMany|hasManyThrough|hasOneThrough)\s*\(',
        body_text
    ))


def get_default_return(rtype, nullable):
    if rtype is None:
        return 'null'
    base = rtype.split('|')[0] if '|' in rtype else rtype
    base = base.lstrip('?')
    if base in RETURN_DEFAULTS:
        return RETURN_DEFAULTS[base]
    if base in RELATIONSHIP_RETURNS:
        return None
    if nullable or rtype.startswith('?'):
        return 'null'
    return 'null'


def wrap_method(lines, minfo):
    ob = minfo['open_brace_line']
    cb = minfo['close_brace_line']
    name = minfo['name']
    indent = minfo['indent']
    rtype = minfo['return_type_clean']
    nullable = minfo['nullable']

    body_indent = indent + '    '
    inner_indent = body_indent + '    '

    body = lines[ob + 1:cb]

    reindented = []
    for bl in body:
        if bl.strip() == '':
            reindented.append('')
        else:
            reindented.append('    ' + bl)

    log_str = "static::class . '::" + name + " \u2014 ' . get_class($e) . ': ' . $e->getMessage()"

    catch_block = [
        body_indent + "} catch (\\Throwable $e) {",
        inner_indent + "Log::error(" + log_str + ", ['file' => $e->getFile(), 'line' => $e->getLine()]);",
    ]

    default_val = get_default_return(rtype, nullable)
    if rtype == 'void' or default_val is None:
        pass
    else:
        catch_block.append(inner_indent + "return " + default_val + ";")

    catch_block.append(body_indent + "}")

    new_body = [body_indent + "try {"] + reindented + catch_block

    return lines[:ob + 1] + new_body + lines[cb:]


def ensure_log_import(content):
    if re.search(r'use\s+Illuminate\\Support\\Facades\\[^;]*\bLog\b', content):
        return content

    class_match = re.search(
        r'^(?:abstract\s+|final\s+)?class\s+\w+|^trait\s+\w+|^interface\s+\w+',
        content, re.MULTILINE
    )
    header = content[:class_match.start()] if class_match else content

    m = re.search(r'(use\s+Illuminate\\Support\\Facades\\\{)([^}]+)(\};)', header)
    if m:
        items = [x.strip() for x in m.group(2).split(',') if x.strip()]
        if 'Log' not in items:
            items.append('Log')
            items.sort()
            new_import = m.group(1) + ', '.join(items) + m.group(3)
            return content.replace(m.group(0), new_import, 1)
        return content

    m = re.search(
        r'(use\s+Illuminate\\Support\\Facades\\\{\s*\n)((?:\s+\w+,?\s*\n)+)(\s*\};)',
        header
    )
    if m:
        items = [x.strip().rstrip(',') for x in m.group(2).strip().split('\n') if x.strip()]
        if 'Log' not in items:
            items.append('Log')
            items.sort()
            inner = ',\n'.join('    ' + it for it in items)
            new_import = m.group(1) + inner + '\n' + m.group(3)
            return content.replace(m.group(0), new_import, 1)
        return content

    m = re.search(r'use\s+Illuminate\\Support\\Facades\\(\w+);', header)
    if m:
        existing = m.group(1)
        items = sorted(set([existing, 'Log']))
        replacement = "use Illuminate\\Support\\Facades\\{" + ", ".join(items) + "};"
        return content.replace(m.group(0), replacement, 1)

    header_lines = header.split('\n')
    last_use_idx = None
    for idx, line in enumerate(header_lines):
        stripped = line.strip()
        if stripped.startswith('use ') and (stripped.endswith(';') or stripped.endswith('};\n') or stripped.rstrip().endswith('};')):
            last_use_idx = idx

    if last_use_idx is not None:
        all_lines = content.split('\n')
        all_lines.insert(last_use_idx + 1, "use Illuminate\\Support\\Facades\\{Log};")
        return '\n'.join(all_lines)

    return content


def remove_debug_output(content):
    lines = content.split('\n')
    cleaned = []
    for line in lines:
        stripped = line.strip()
        if re.match(r'^(echo|print_r|var_dump|dd|dump)\s*[\(\'"$]', stripped):
            continue
        if re.match(r'^(echo|print_r|var_dump|dd|dump)\s+', stripped):
            continue
        cleaned.append(line)
    return '\n'.join(cleaned)


def process_file(filepath):
    with open(filepath) as f:
        content = f.read()
    original = content

    content = remove_debug_output(content)

    lines = content.split('\n')
    methods = find_methods(lines)

    wrappable = []
    for m in methods:
        if m['name'] in SKIP_METHODS or m['name'].startswith('__'):
            continue
        if m['name'].startswith('scope'):
            continue
        ob = m['open_brace_line']
        cb = m['close_brace_line']
        if body_has_try_catch(lines, ob, cb):
            continue
        if is_relationship(lines, ob, cb):
            continue
        if is_simple_delegation(lines, ob, cb):
            continue
        rt = m.get('return_type_clean', '')
        if rt and rt in RELATIONSHIP_RETURNS:
            continue
        wrappable.append(m)

    modified = False
    for m in reversed(wrappable):
        lines = wrap_method(lines, m)
        modified = True

    content = '\n'.join(lines)

    if modified:
        content = ensure_log_import(content)

    if content != original:
        with open(filepath, 'w') as f:
            f.write(content)
        return True
    return False


def main():
    total = 0
    changed = 0
    errors = 0
    for d in DIRS:
        full_dir = os.path.join(BASE, d)
        for root, dirs, files in os.walk(full_dir):
            dirs.sort()
            for fname in sorted(files):
                if not fname.endswith('.php'):
                    continue
                filepath = os.path.join(root, fname)
                total += 1
                try:
                    if process_file(filepath):
                        changed += 1
                        print(f"  MODIFIED: {fname}")
                    else:
                        print(f"  OK: {fname}")
                except Exception as ex:
                    errors += 1
                    print(f"  ERROR: {fname}: {ex}")

    print(f"\nDone. {changed}/{total} files modified, {errors} errors.")


if __name__ == '__main__':
    main()
