#!/usr/bin/env python3
"""
Fix all PHPStan duplicate-key and undefined-constant issues in LangsConstants.php.

Issues fixed:
1. Undefined constants: DDT_OP -> DDT_OPT, JRN_ENT -> JRN_ET, NFT_TMP -> NTF_TMP
2. Duplicate ViewsConstants::BDG blocks -> merge into one
3. Duplicate ViewsConstants::CPL blocks -> merge into one
4. Duplicate literal keys within language sub-arrays -> remove first occurrence
"""

import re
import sys
import os

filepath = os.path.join(os.path.dirname(__file__), 'app/Config/Constants/LangsConstants.php')

with open(filepath, 'r') as f:
    lines = f.readlines()

original_count = len(lines)
print(f"Read {original_count} lines from {filepath}")

# ============================================================
# STEP 1: Fix undefined constants (no line count changes)
# ============================================================
const_fixes = 0
for i in range(len(lines)):
    orig = lines[i]
    # DDT_OP -> DDT_OPT (avoid matching existing DDT_OPT)
    if 'ViewsConstants::DDT_OP' in lines[i] and 'ViewsConstants::DDT_OPT' not in lines[i]:
        lines[i] = lines[i].replace('ViewsConstants::DDT_OP', 'ViewsConstants::DDT_OPT')
    # JRN_ENT -> JRN_ET
    if 'ViewsConstants::JRN_ENT' in lines[i]:
        lines[i] = lines[i].replace('ViewsConstants::JRN_ENT', 'ViewsConstants::JRN_ET')
    # NFT_TMP -> NTF_TMP
    if 'ViewsConstants::NFT_TMP' in lines[i]:
        lines[i] = lines[i].replace('ViewsConstants::NFT_TMP', 'ViewsConstants::NTF_TMP')
    if lines[i] != orig:
        const_fixes += 1
        print(f"  Fixed constant at line {i+1}")

print(f"Step 1: Fixed {const_fixes} undefined constant references")

# ============================================================
# STEP 2: Remove duplicate literal keys within language sub-arrays
# These are string keys that appear twice in the same array.
# We remove the FIRST occurrence (PHP would use the last one anyway).
# ============================================================

# Known duplicate literal keys (from PHPStan report)
dup_keys = [
    'invoice_product_route_unavailable',
    'payment_status_unavailable',
    'delete_order_unavailable',
    'download_monthly_purchase_unavailable',
    'system_ip_edit_route_unavailable',
]

lines_to_delete = set()

for dup_key in dup_keys:
    # Find all line indices containing this key
    occurrences = []
    for i, line in enumerate(lines):
        if f"'{dup_key}'" in line:
            occurrences.append(i)
    
    if len(occurrences) < 2:
        continue
    
    # Group into pairs: (first_occurrence, second_occurrence) within same language block
    # Consecutive pairs belong to the same language sub-array
    if len(occurrences) % 2 != 0:
        print(f"  Warning: Odd number of '{dup_key}' occurrences ({len(occurrences)}), skipping")
        continue
    
    for j in range(0, len(occurrences), 2):
        first = occurrences[j]
        second = occurrences[j + 1]
        # Verify they're close together (same language block)
        if second - first > 100:
            print(f"  Warning: '{dup_key}' pair too far apart (lines {first+1} and {second+1}), skipping")
            continue
        lines_to_delete.add(first)

# Delete lines from bottom to top
deleted_count = 0
for idx in sorted(lines_to_delete, reverse=True):
    # Before deleting, check if this will break comma syntax
    # If the line before ends without a comma and this line has one, we may need to adjust
    line_content = lines[idx].strip()
    del lines[idx]
    deleted_count += 1

print(f"Step 2: Marked and deleted {deleted_count} duplicate literal key lines")

# ============================================================
# STEP 3: Merge duplicate ViewsConstants blocks (BDG and CPL)
# ============================================================

def find_block_starts(lines, const_name):
    """Find all line indices where ViewsConstants::CONST => [ appears"""
    pattern = re.compile(rf'ViewsConstants::{const_name}\s*=>\s*\[')
    return [i for i, line in enumerate(lines) if pattern.search(line)]

def find_block_end(lines, start_idx):
    """Find the end of a bracket-balanced block starting at start_idx"""
    depth = 0
    for i in range(start_idx, len(lines)):
        depth += lines[i].count('[') - lines[i].count(']')
        if depth == 0 and i > start_idx:
            return i
        if depth == 0 and i == start_idx:
            # Single line block - check if brackets balance on this line
            if '[' in lines[i] and ']' in lines[i]:
                return i
    return None

def extract_block1_entries(lines, start, end):
    """
    Extract language entries from a block.
    Returns dict: {lang_code: list_of_entry_lines}
    Entry lines are the raw text of key => value entries (without lang wrapper).
    """
    result = {}
    current_lang = None
    
    for i in range(start + 1, end + 1):
        stripped = lines[i].strip()
        
        # Match language array opening: 'ar' => [
        lang_match = re.match(r"'([a-z][\w-]*)'\s*=>\s*\[(.*)$", stripped)
        if lang_match:
            current_lang = lang_match.group(1)
            if current_lang not in result:
                result[current_lang] = []
            rest = lang_match.group(2)
            # Check for inline entries: 'ar' => ['key' => 'value']
            if "=>" in rest:
                # Extract just the key => value parts
                inner_matches = re.findall(r"'(\w+)'\s*=>\s*('.*?'\s*(?:\.\s*self::\w+)?)", rest)
                for key, val in inner_matches:
                    result[current_lang].append((key, val.rstrip(',')))
            continue
        
        # Match key => value entries
        if current_lang and "=>" in stripped:
            entry_match = re.match(r"'(\w+)'\s*=>\s*(.+?)(?:,\s*)?$", stripped)
            if entry_match:
                key = entry_match.group(1)
                val = entry_match.group(2).rstrip(',').rstrip()
                result[current_lang].append((key, val))
        
        # End of language sub-array
        if stripped in ('],', ']'):
            current_lang = None
    
    return result

for const_name in ['BDG', 'CPL']:
    starts = find_block_starts(lines, const_name)
    if len(starts) != 2:
        print(f"  Warning: Expected 2 {const_name} blocks, found {len(starts)}")
        continue
    
    b1_start = starts[0]
    b1_end = find_block_end(lines, b1_start)
    b2_start = starts[1]
    b2_end = find_block_end(lines, b2_start)
    
    if b1_end is None or b2_end is None:
        print(f"  Warning: Could not find end of {const_name} block")
        continue
    
    print(f"  {const_name} block 1: lines {b1_start+1}-{b1_end+1} ({b1_end-b1_start+1} lines)")
    print(f"  {const_name} block 2: lines {b2_start+1}-{b2_end+1} ({b2_end-b2_start+1} lines)")
    
    # Extract entries from block 1
    b1_entries = extract_block1_entries(lines, b1_start, b1_end)
    print(f"  Block 1 has entries for {len(b1_entries)} languages: {list(b1_entries.keys())}")
    
    # Add block 1 entries to block 2 (insert after each language's opening line)
    # First, find language array openings in block 2
    insert_targets = []  # (line_idx, lang_code)
    for i in range(b2_start + 1, b2_end + 1):
        stripped = lines[i].strip()
        lang_match = re.match(r"'([a-z][\w-]*)'\s*=>\s*\[", stripped)
        if lang_match:
            lang_code = lang_match.group(1)
            if lang_code in b1_entries:
                insert_targets.append((i, lang_code))
    
    # Insert from bottom to top
    inserted_count = 0
    for line_idx, lang_code in reversed(insert_targets):
        entries = b1_entries[lang_code]
        # Determine indentation from the next line
        if line_idx + 1 < len(lines):
            next_line = lines[line_idx + 1]
            indent_match = re.match(r'(\s*)', next_line)
            indent = indent_match.group(1) if indent_match else '\t\t\t\t'
        else:
            indent = '\t\t\t\t'
        
        # Insert entries after the language opening line
        for j, (key, val) in enumerate(entries):
            new_line = f"{indent}'{key}' => {val},\n"
            lines.insert(line_idx + 1 + j, new_line)
            inserted_count += 1
    
    print(f"  Inserted {inserted_count} entries from block 1 into block 2")
    
    # Delete block 1 (it's before block 2, so indices haven't shifted)
    block1_size = b1_end - b1_start + 1
    del lines[b1_start:b1_end + 1]
    print(f"  Deleted block 1 ({block1_size} lines)")

print(f"Step 3: Merged duplicate BDG and CPL blocks")

# ============================================================
# STEP 4: Verify and write
# ============================================================

# Quick syntax check: ensure brackets are roughly balanced
open_count = sum(line.count('[') for line in lines)
close_count = sum(line.count(']') for line in lines)
print(f"\nBracket check: [ = {open_count}, ] = {close_count}, diff = {open_count - close_count}")

with open(filepath, 'w') as f:
    f.writelines(lines)

final_count = len(lines)
print(f"\nDone! Original: {original_count} lines -> New: {final_count} lines (delta: {final_count - original_count})")
