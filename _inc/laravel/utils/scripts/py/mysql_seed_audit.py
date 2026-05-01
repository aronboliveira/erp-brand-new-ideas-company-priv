#!/usr/bin/env python3
"""
mysql_seed_audit.py — Audit MySQL seed state against Laravel seeders.

Reads DatabaseSeeder.php to discover all registered seeders, maps them
to expected tables, then verifies each table has data. Outputs a JSON
report suitable for CI pipelines.

Usage:
    python3 mysql_seed_audit.py [--json] [--run-seeds] [--verbose]

Env overrides: DB_HOST, DB_PORT, DB_USER, DB_PASS, DB_NAME
"""
import argparse
import json
import os
import re
import subprocess
import sys
from pathlib import Path
from typing import Any

try:
    import mysql.connector as mc
except ImportError:
    mc = None

# ── Config ────────────────────────────────────────────────────
DB_HOST = os.getenv("DB_HOST", "127.0.0.1")
DB_PORT = int(os.getenv("DB_PORT", "3306"))
DB_USER = os.getenv("DB_USER", "admin_brand_new_ideas_company")
DB_PASS = os.getenv("DB_PASS", "76562f3A*@brandnewideascompany")
DB_NAME = os.getenv("DB_NAME", "erp_brand_new_ideas_company")

SCRIPT_DIR = Path(__file__).resolve().parent
LARAVEL_ROOT = SCRIPT_DIR / ".." / ".." / ".."
SEEDER_FILE = LARAVEL_ROOT / "database" / "seeders" / "DatabaseSeeder.php"

# Map seeder class names → expected table names (common patterns)
SEEDER_TABLE_MAP: dict[str, str] = {
    "UsersTableSeeder": "users",
    "UserSeeder": "users",
    "NotificationSeeder": "notifications",
    "PlansTableSeeder": "plans",
    "AiTemplateSeeder": "ai_templates",
    "BranchSeeder": "branches",
    "DepartmentSeeder": "departments",
    "DesignationSeeder": "designations",
    "ScheduleSeeder": "schedules",
    "LanguageSeeder": "languages",
    "PipelineSeeder": "pipelines",
    "StageSeeder": "stages",
    "LabelSeeder": "labels",
    "SourceSeeder": "sources",
    "ProjectStageSeeder": "project_stages",
    "SettingsSeeder": "settings",
    "ClientSeeder": "clients",
}


def _camel_to_snake(name: str) -> str:
    """Convert CamelCase to snake_case."""
    s1 = re.sub(r"(.)([A-Z][a-z]+)", r"\1_\2", name)
    return re.sub(r"([a-z0-9])([A-Z])", r"\1_\2", s1).lower()


def guess_table(seeder_name: str) -> str:
    """Guess the table name from a Seeder class name."""
    if seeder_name in SEEDER_TABLE_MAP:
        return SEEDER_TABLE_MAP[seeder_name]
    base = seeder_name.replace("TableSeeder", "").replace("Seeder", "")
    snake = _camel_to_snake(base)
    # Pluralise naively
    if not snake.endswith("s"):
        snake += "s"
    return snake


def parse_seeders() -> list[str]:
    """Extract seeder class names from DatabaseSeeder.php."""
    if not SEEDER_FILE.exists():
        print(f"[WARN] Seeder file not found: {SEEDER_FILE}", file=sys.stderr)
        return []
    text = SEEDER_FILE.read_text()
    return re.findall(r"(\w+Seeder)::class", text)


def get_connection() -> Any:
    """Create a MySQL connection using mysql-connector-python or CLI fallback."""
    if mc:
        return mc.connect(
            host=DB_HOST, port=DB_PORT, user=DB_USER,
            password=DB_PASS, database=DB_NAME, connect_timeout=10
        )
    return None


def query_cli(sql: str) -> str:
    """Fallback: run query via mysql CLI."""
    cmd = [
        "mysql", f"-u{DB_USER}", f"-p{DB_PASS}",
        f"-h{DB_HOST}", f"-P{DB_PORT}",
        "--batch", "--skip-column-names",
        DB_NAME, "-e", sql
    ]
    result = subprocess.run(cmd, capture_output=True, text=True, timeout=30)
    return result.stdout.strip()


def get_table_counts() -> dict[str, int]:
    """Return {table_name: row_count} for all tables."""
    conn = get_connection()
    tables: dict[str, int] = {}

    if conn:
        cursor = conn.cursor()
        cursor.execute(
            "SELECT TABLE_NAME FROM information_schema.TABLES "
            "WHERE TABLE_SCHEMA=%s AND TABLE_TYPE='BASE TABLE'",
            (DB_NAME,)
        )
        table_names = [r[0] for r in cursor.fetchall()]
        for tbl in table_names:
            cursor.execute(f"SELECT COUNT(*) FROM `{tbl}`")  # noqa: S608
            tables[tbl] = cursor.fetchone()[0]
        cursor.close()
        conn.close()
    else:
        raw = query_cli(
            "SELECT TABLE_NAME FROM information_schema.TABLES "
            f"WHERE TABLE_SCHEMA='{DB_NAME}' AND TABLE_TYPE='BASE TABLE'"
        )
        table_names = raw.split("\n") if raw else []
        for tbl in table_names:
            tbl = tbl.strip()
            if not tbl:
                continue
            cnt_raw = query_cli(f"SELECT COUNT(*) FROM `{tbl}`")
            tables[tbl] = int(cnt_raw) if cnt_raw.isdigit() else 0
    return tables


def run_seeds() -> bool:
    """Run php artisan db:seed."""
    artisan = LARAVEL_ROOT / "artisan"
    if not artisan.exists():
        print("[ERROR] artisan not found", file=sys.stderr)
        return False
    result = subprocess.run(
        ["php", str(artisan), "db:seed", "--force"],
        capture_output=True, text=True, cwd=str(LARAVEL_ROOT), timeout=120
    )
    if result.returncode != 0:
        print(f"[ERROR] Seeds failed:\n{result.stderr}", file=sys.stderr)
        return False
    print(result.stdout)
    return True


def main() -> None:
    parser = argparse.ArgumentParser(description="Audit MySQL seed state")
    parser.add_argument("--json", action="store_true", help="JSON output")
    parser.add_argument("--run-seeds", action="store_true", help="Run db:seed before audit")
    parser.add_argument("--verbose", "-v", action="store_true", help="Show all tables")
    args = parser.parse_args()

    if args.run_seeds:
        if not run_seeds():
            sys.exit(1)

    seeders = parse_seeders()
    table_counts = get_table_counts()

    # Build expected → actual mapping
    results: list[dict[str, Any]] = []
    seeded_tables: set[str] = set()

    for seeder in seeders:
        expected_table = guess_table(seeder)
        actual_count = table_counts.get(expected_table)
        exists = expected_table in table_counts
        has_data = actual_count is not None and actual_count > 0
        seeded_tables.add(expected_table)

        status = "ok" if has_data else ("empty" if exists else "missing")
        results.append({
            "seeder": seeder,
            "table": expected_table,
            "exists": exists,
            "rows": actual_count or 0,
            "status": status,
        })

    # Tables not covered by any seeder
    uncovered = []
    for tbl, cnt in sorted(table_counts.items()):
        if tbl not in seeded_tables:
            uncovered.append({"table": tbl, "rows": cnt})

    report: dict[str, Any] = {
        "database": DB_NAME,
        "host": f"{DB_HOST}:{DB_PORT}",
        "total_tables": len(table_counts),
        "total_seeders": len(seeders),
        "seeder_audit": results,
        "uncovered_tables": uncovered,
        "summary": {
            "ok": sum(1 for r in results if r["status"] == "ok"),
            "empty": sum(1 for r in results if r["status"] == "empty"),
            "missing": sum(1 for r in results if r["status"] == "missing"),
        },
    }

    if args.json:
        print(json.dumps(report, indent=2))
    else:
        print(f"\n{'═' * 60}")
        print(f" Seed Audit: {DB_NAME} @ {DB_HOST}:{DB_PORT}")
        print(f" Tables: {len(table_counts)}  |  Seeders found: {len(seeders)}")
        print(f"{'═' * 60}\n")

        for r in results:
            icon = "✅" if r["status"] == "ok" else ("⚠️ " if r["status"] == "empty" else "❌")
            print(f" {icon} {r['seeder']:40s} → {r['table']:30s} [{r['rows']:>6d} rows] {r['status'].upper()}")

        if uncovered and args.verbose:
            print(f"\n{'─' * 60}")
            print(" Tables without a known seeder:")
            for u in uncovered:
                print(f"   {u['table']:30s} [{u['rows']:>6d} rows]")

        s = report["summary"]
        print(f"\n{'─' * 60}")
        print(f" ✅ OK: {s['ok']}   ⚠️  Empty: {s['empty']}   ❌ Missing: {s['missing']}")
        print(f"{'═' * 60}\n")

    if report["summary"]["missing"] > 0:
        sys.exit(2)
    if report["summary"]["empty"] > 0:
        sys.exit(1)
    sys.exit(0)


if __name__ == "__main__":
    main()
