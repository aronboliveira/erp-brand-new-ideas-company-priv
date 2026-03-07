#!/usr/bin/env python3
import shutil
import os
import re
from pathlib import Path
from colorama import init, Fore
def to_pascal_case(s: str) -> str:
    return ''.join(word.capitalize() for word in s.split('_'))
def to_camel_case(s: str) -> str:
    p = to_pascal_case(s)
    return p[0].lower() + p[1:] if p else p
def main() -> None:
    init(autoreset=True)
    reference_input = input("Enter reference (Python models) path: ").strip()
    reference: Path
    if re.match(r'^[A-Z]:\\', reference_input): reference = Path(reference_input)
    else: reference = Path.cwd() / reference_input
    working_input = input("Enter working (PHP models root) path: ").strip()
    working: Path
    if re.match(r'^[A-Z]:\\', working_input): working = Path(working_input)
    else: working = (Path.cwd() / working_input) if not working_input == '.' else Path.cwd()
    if not reference.is_dir():
        print(Fore.RED + f"Reference path not found or not a directory: {reference}")
        return
    if not working.is_dir():
        print(Fore.RED + f"Working path not found or not a directory: {working}")
        return
    for dirpath, _, filenames in os.walk(reference):
        for fname in filenames:
            if not fname.endswith(".py"):
                continue
            py_path = Path(dirpath) / fname
            rel = py_path.relative_to(reference)
            rel_dir = rel.parent
            base = py_path.stem
            variants = [
                f"{base}.php",
                f"{to_pascal_case(base)}.php",
                f"{to_camel_case(base)}.php",
            ]
            found = None
            for name in variants:
                hits = list(working.rglob(name))
                if hits:
                    found = hits[0]
                    break
            if not found:
                print(Fore.YELLOW + f"✖ No PHP file for: {base} (tried {', '.join(variants)})")
                continue

            dest_dir = working / rel_dir
            dest = dest_dir / found.name
            try:
                os.makedirs(dest_dir, exist_ok=True)
                shutil.move(str(found), str(dest))
                print(Fore.GREEN + f"✔ Moved: {found} → {dest}")
            except Exception as e:
                print(Fore.RED + f"✖ Failed to move {found.name}: {e}")
                continue

            if all(not part.startswith("_") for part in rel_dir.parts):
                new_name = f"{to_pascal_case(base)}.php"
                new_dest = dest_dir / new_name
                if dest.name != new_name:
                    try:
                        dest.rename(new_dest)
                        print(Fore.CYAN + f"ℹ Renamed: {dest.name} → {new_dest.name}")
                    except Exception as e:
                        print(Fore.RED + f"✖ Failed to rename {dest.name}: {e}")


if __name__ == "__main__":
    main()
