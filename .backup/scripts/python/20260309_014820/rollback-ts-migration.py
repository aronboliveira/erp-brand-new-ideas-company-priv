#!/usr/bin/env python3
"""
rollback-ts-migration.py — Revert TypeScript migration changes.

Usage:
    python3 .backup/scripts/python/20260309_014820/rollback-ts-migration.py [--dry-run]
"""

import argparse
import os
import shutil
import sys
from pathlib import Path


def log(msg: str) -> None:
    print(f"[rollback] {msg}")


def find_workspace() -> Path:
    """Walk up from this script to find the workspace root (has _inc/ dir)."""
    p = Path(__file__).resolve().parent
    for _ in range(10):
        if (p / "_inc").is_dir():
            return p
        p = p.parent
    raise RuntimeError("Cannot find workspace root (no _inc/ directory found)")


def main() -> None:
    parser = argparse.ArgumentParser(description="Rollback TS migration")
    parser.add_argument("--dry-run", action="store_true", help="Show what would be done")
    args = parser.parse_args()

    workspace = find_workspace()
    ts_dir = workspace / "_inc" / "laravel" / "ts"

    log(f"Workspace: {workspace}")
    log(f"TS dir:    {ts_dir}")
    if args.dry_run:
        log("** DRY RUN — no changes will be made **")

    removed = 0

    # 1. Remove dist/ and dist-iife/
    for d in ["dist", "dist-iife"]:
        target = ts_dir / d
        if target.is_dir():
            log(f"Removing {d}/...")
            if not args.dry_run:
                shutil.rmtree(target)
            removed += 1

    # 2. Remove lang TS files
    routes_dir = ts_dir / "src" / "public" / "assets" / "js" / "routes"
    if routes_dir.is_dir():
        lang_files = list(routes_dir.rglob("lang/*.ts"))
        log(f"Found {len(lang_files)} lang TS files")
        if not args.dry_run:
            for f in lang_files:
                f.unlink()
            # Remove empty lang/ dirs
            for d in routes_dir.rglob("lang"):
                if d.is_dir() and not any(d.iterdir()):
                    d.rmdir()
        removed += len(lang_files)

    # 3. Remove core singletons
    core_dir = ts_dir / "src" / "public" / "assets" / "js" / "core"
    if core_dir.is_dir():
        log("Removing core TS singletons...")
        if not args.dry_run:
            shutil.rmtree(core_dir)
        removed += 1

    # 4. Remove integration tests
    integ_dir = ts_dir / "tests" / "integration"
    if integ_dir.is_dir():
        log("Removing integration tests...")
        if not args.dry_run:
            shutil.rmtree(integ_dir)
        removed += 1

    # 5. Remove ESM→IIFE script
    esm_script = ts_dir / "scripts" / "esm-to-iife.cjs"
    if esm_script.is_file():
        log("Removing ESM→IIFE script...")
        if not args.dry_run:
            esm_script.unlink()
        removed += 1

    log("")
    log(f"Rollback complete. {removed} items removed.")
    log("Original JS files in public/assets/js/routes/ are untouched.")


if __name__ == "__main__":
    main()
