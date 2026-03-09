#!/usr/bin/env python3

import os, re, sys, traceback
from typing import Dict, List, Tuple, Optional
from time import sleep
from colorama import Fore, init

def are_equivalent_paths(rel1: str, rel2: str) -> bool:
    """
    Return True if rel1 and rel2 refer to the *same* file
    up to at most one extra directory inserted just before the filename.
    """
    if rel1 == rel2: return True
    name1, name2 = (re.sub(r'[-_]', '', os.path.basename(r).lower()) for r in (rel1, rel2))
    if name1 != name2: return False
    dirs1, dirs2 = map(lambda path: [
      	re.sub(r'[-_]', '', d.lower()) for d in os.path.normpath(path).split(os.path.sep)[:-1]],
        (rel1, rel2))
    print(f'Testing {dirs1} - [{dirs1[:len(dirs2)]}] || {dirs2} - [{dirs2[:len(dirs1)]}]')
    if dirs2[:len(dirs1)] == dirs1 or dirs1[:len(dirs2)] == dirs2:
        return True
    return False
  
def adjust_path_type(path:str) -> str:
  return os.path.abspath(path.strip().strip('"').strip("'"))

class MethodInfo:
  name: str
  return_type: Optional[str]
  relations: List[str]
  start_line: int
  end_line: int
  def __init__(self, name, return_type, relations, start_line, end_line) -> None:
    self.name = name
    self.return_type = return_type
    self.relations = relations
    self.start_line = start_line
    self.end_line = end_line

def get_relations_methods_data(path:str) -> Dict[str, MethodInfo]:
  ALLOWED_RELATION_CHAINS = [
    'withDefault',
    'withoutDefault',
    'touches',
    'withoutGlobalScopes',
    'withoutGlobalScope',
    'withPivot',
    'withTimestamps',
    'wherePivot',
    'orWherePivot',
    'wherePivotIn',
    'wherePivotNotIn',
    'wherePivotNull',
    'wherePivotNotNull',
    'orderBy',
    'limit',
    'skip',
    'take',
    'latest',
    'oldest'
	]
  allowed = '|'.join(ALLOWED_RELATION_CHAINS)
  methods_pattern = re.compile(
		r'^\s*(?:public|private|protected)?\s+function\s+&?\s*'
  	r'(?P<name>[A-Za-z_]\w*)\s*\(.*\)'
    r'(?:\s*:\s*(?P<ret>[\?\w\\]+))?'
	)
  relation_pattern = re.compile(
    r'return\s+\$this->'
    r'(hasOne|hasMany|belongsTo|belongsToMany|morphOne|morphTo|morphMany)'
    r'\s*\([^)]*\)'
		r'('
       r'(?:->\s*(?:' + allowed + r')\s*\([^)]*\)\s*)*'
    r')'   
    r'\s*;',
    re.IGNORECASE
	)
  methods_defs: Dict[str, MethodInfo] = {}
  with open(path, 'r', encoding='utf-8') as f:
    lines = f.readlines()
  i, total = 0, len(lines)
  while i < total:
    l = lines[i]
    m = methods_pattern.match(l)
    if not m:
      i += 1
      continue
    nm = m.group('name')
    ret_type = m.group('ret') if m.group('ret') else None
    j = i + 1
    if ret_type:
      short = ret_type.rsplit('\\', 1)[-1].lstrip('?').lower()
      if short in ('hasone','hasmany','belongsto','belongstomany',
                   'morphone','morphto','morphmany'):
        k = re.sub(r'[-_]', '', nm.lower())
        methods_defs[k] = MethodInfo(name=nm, return_type=ret_type, relations=[short],
                                     start_line=i+1, end_line=j)
        i = j
        continue
    start_line, brace_count = i + 1, 0
    brace_count += l.count('{') - l.count('}')
    while j < total and brace_count == 0:
      brace_count += lines[j].count('{') - lines[j].count('}')
      j += 1
    relations: List[str] = []
    while j < total and brace_count > 0:
        rl = lines[j]
        relm = relation_pattern.search(rl)
        if relm:
          parts = [relm.group(1)] + re.findall(r'->\s*([A-Za-z_]\w*)', relm.group(2) or '')
          parts[0] = parts[0].capitalize()
          relations.append('->'.join(parts))
        brace_count += rl.count('{') - rl.count('}')
        j += 1
    if relations:
        k = re.sub(r'[-_]', '', nm.lower())
        methods_defs[k] = MethodInfo(name=nm, return_type=ret_type, relations=relations,
																		start_line=start_line, end_line=j)
    i = j
  return methods_defs

def main():
  init(autoreset=True)
  secs = 3
  print(Fore.GREEN + "You executed the file comparison between two Laravel Model Modules methods.\n"
          f"Press Ctrl+C to exit, otherwise wait for {secs} seconds...")
  sleep(secs)
  try:
    print(Fore.YELLOW + "Starting procedure...")
    first_root = adjust_path_type(input(Fore.CYAN + "Enter the ORIGINAL directory path:\n"))
    second_root = adjust_path_type(input(Fore.CYAN + "Enter the DERIVED directory path:\n"))
    for d in (first_root, second_root):
      if not os.path.isdir(d):
        raise ValueError(f'The given path {d!r} is not a directory.')
    first_php, second_php = [], []
    php_map = ((first_root, first_php), (second_root, second_php))
    for b, coll in php_map:
      for r, _, fs in os.walk(b):
        for f in fs:
          if f.endswith('.php'):
            coll.append(os.path.join(r, f))
    first_rel, second_rel = ([os.path.relpath(p, r) for p in coll] for r, coll in php_map)
    pairs: List[Tuple[str, str, str, str]] = []
    for f in first_rel:
      for s in second_rel:
        if are_equivalent_paths(f, s):
          pairs.append((
							f, s,
							os.path.join(first_root, f),
							os.path.join(second_root, s)
					))
          break
    if not pairs:
      print(Fore.YELLOW + "No matching PHP class files found in both directories.")
      return
    statuses: Dict[str, str] = {}
    print()
    eqs, adds, lck, errs = [], [], [], []
    for _, s_rel, p1, p2 in pairs:
      try:
        m1 = get_relations_methods_data(p1)
      except Exception as e:
        errs.append(Fore.RED + f'Error reading {p1}: {e}')
        trace = traceback.format_exc()
        print(trace)
        m1 = []
      try:
        m2 = get_relations_methods_data(p2)
      except Exception as e:
        errs.append(Fore.RED + f'Error reading {p2}: {e}')
        trace = traceback.format_exc()
        print(trace)
        m2 = []
      if not m1 or not m2:
        status, color = 'Error', Fore.YELLOW
        statuses[s_rel] = status
        errs.append(Fore.YELLOW + f'{s_rel}: No methods extracted')
        continue
      l_m1, l_m2 = (len(m) for m in (m1, m2))
      if l_m1 <= l_m2 and all(name in m2 for name in m1):
        if l_m1 == l_m2:
          status, color = 'Equal correlations', Fore.GREEN
        else:
          status, color = 'Added correlations with no issues', Fore.LIGHTYELLOW_EX
      else:
        status, color = 'LACKING CORRELATIONS', Fore.RED
      statuses[s_rel] = status
      eval_res = color + f'{s_rel}: {l_m1} vs {l_m2} => {status}'
      if color == Fore.RED:
        lck.append(eval_res)
      elif color == Fore.LIGHTYELLOW_EX:
        adds.append(eval_res)
      elif color == Fore.GREEN:
        eqs.append(eval_res)
      else:
        errs.append(eval_res)
    total = len(pairs)
    eq, add, lack, err = (sum(1 for s in statuses.values() if s == v) for v in ('Equal', 'Added methods', 'LACKING METHODS', 'Error'))
    print()
    for r in (*eqs, *errs, *adds, *lck):
      print(r)
    print()
    print(Fore.CYAN + f'Compared {total} file(s):\n{eq} equal\n{add} added\n{lack} lacking\n{err} errors')
  except KeyboardInterrupt:
    print(Fore.YELLOW + "\nOperation cancelled by user.")
    sys.exit(1)
  except TypeError as e:
    print(Fore.RED + f"TypeError: {e}")
    trace = traceback.format_exc()
    print(trace)
  except PermissionError as e:
    print(Fore.RED + f"PermissionError: {e}")
    trace = traceback.format_exc()
    print(trace)
  except Exception as e:
    print(Fore.RED + f"An unexpected error occurred: {e}")
    trace = traceback.format_exc()
    print(trace)


if __name__ == '__main__':
  main()