#!/usr/bin/env python3
import shutil
import os
from pathlib import Path
from colorama import init, Fore

def main():
	init(autoreset=True)
	reference = input("Enter reference path (structure to mirror): ").strip()
	if os.path.isabs(reference): reference = Path(reference)
	else: reference = Path.cwd() / reference
	target = input("Enter target path (directory to reorganize): ").strip()
	if os.path.isabs(target): 
			target = Path(target)
	else:
			target = (Path.cwd() / target) if not target == '.' else Path.cwd()
	if not reference.is_dir():
			print(Fore.RED + f"Reference path not found or not a directory: {reference}")
			return
	if not target.is_dir():
			print(Fore.RED + f"Target path not found or not a directory: {target}")
			return
	reference_files = {}
	for dirpath, _, filenames in os.walk(reference):
			rel_path = Path(dirpath).relative_to(reference)
			for filename in filenames:
					reference_files[filename] = rel_path
	target_files = []
	for dirpath, _, filenames in os.walk(target):
			for filename in filenames:
					file_path = Path(dirpath) / filename
					target_files.append((filename, file_path))
	for filename, file_path in target_files:
		if filename in reference_files:
				expected_rel = reference_files[filename]
				current_rel = file_path.parent.relative_to(target)
				if len(current_rel.parts) > len(expected_rel.parts):
						print(Fore.YELLOW + 
									f"⚠ Skipping nested file: {file_path} "
									f"(depth {len(current_rel.parts)} > expected {len(expected_rel.parts)})")
						continue
				dest_dir  = target / expected_rel
				dest_file = dest_dir / filename

				if file_path == dest_file:
						print(Fore.CYAN + f"ℹ Already in correct location: {file_path}")
						continue

				if not dest_dir.exists():
						try:
								dest_dir.mkdir(parents=True, exist_ok=True)
								print(Fore.GREEN + f"✔ Created directory: {dest_dir}")
						except Exception as e:
								print(Fore.RED + f"✖ Failed to create directory {dest_dir}: {e}")
								continue

				try:
						shutil.move(str(file_path), str(dest_file))
						print(Fore.GREEN + f"✔ Moved: {file_path} → {dest_file}")
				except Exception as e:
						print(Fore.RED + f"✖ Failed to move {filename}: {e}")
		else:
				print(Fore.YELLOW + f"⚠ No matching structure for: {filename} (leaving at current location)")


if __name__ == "__main__": main()
