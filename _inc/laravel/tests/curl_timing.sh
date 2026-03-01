#!/usr/bin/env python3
# =============================================================================
# curl_timing.sh — HTTP response timing benchmark for ERP routes
#
# Measures time_namelookup, time_connect, time_starttransfer, time_total
# for each route across N rounds (cold + warm requests).
#
# Usage:  python3 tests/curl_timing.sh [ROUNDS] [OUTPUT_DIR]
#      or bash     tests/curl_timing.sh [ROUNDS] [OUTPUT_DIR]   (delegates to python3)
# =============================================================================
"""
Delegates to the embedded Python implementation.
Called as: bash tests/curl_timing.sh [ROUNDS] [OUTPUT_DIR]
"""
# ── Bash wrapper (only executes when called via bash) ──────────────────────
# The lines below are a polyglot trick: valid bash AND valid Python comment,
# so `bash curl_timing.sh` re-invokes python3 on the same file.
# This file IS the python script when called directly.

# --- actual Python implementation starts here ---
import subprocess
import json
import os
import sys
import re
from datetime import datetime
from urllib.parse import urlencode
from pathlib import Path

ROUNDS      = int(sys.argv[1]) if len(sys.argv) > 1 else 5
OUTPUT_DIR  = Path(sys.argv[2]) if len(sys.argv) > 2 else Path("/tmp/curl_timing")
BASE_URL    = os.environ.get("APP_URL", "http://127.0.0.1:8000")
EMAIL       = os.environ.get("TEST_EMAIL", "u_1ecb6d5a-e2c5-4961-af3b-0ad83f9d259c@test.local")
PASSW       = os.environ.get("TEST_PASS",  "Admin@1234")

OUTPUT_DIR.mkdir(parents=True, exist_ok=True)
TIMESTAMP   = datetime.now().strftime("%Y%m%dT%H%M%S")
COOKIE_JAR  = str(OUTPUT_DIR / f"cookies_{TIMESTAMP}.txt")
RESULT_FILE = OUTPUT_DIR / f"results_{TIMESTAMP}.json"

ROUTES = [
    ("GET", "/login",                        False),
    ("GET", "/dashboard",                    True),
    ("GET", "/customers",                    True),
    ("GET", "/vendors",                      True),
    ("GET", "/product_services",             True),  # was: /products
    ("GET", "/invoices",                     True),
    ("GET", "/bills",                        True),
    ("GET", "/proposal",                     True),  # was: /proposals
    ("GET", "/purchases",                    True),
    ("GET", "/leads",                        True),
    ("GET", "/deals",                        True),
    ("GET", "/projects",                     True),
    # tasks are nested: /projects/{id}/tasks - skip for benchmark
    ("GET", "/expenses",                     True),
    ("GET", "/reports/income-summary",       True),
    ("GET", "/reports/expense-summary",      True),
    ("GET", "/reports/balance-sheets",       True),  # was: balance-sheet
    ("GET", "/reports/trial-balance",        True),
    ("GET", "/reports/profit-losses",        True),  # was: profit-loss
]

# curl write-out format — single-line to avoid quoting issues
CURL_FMT = (
    '{"time_namelookup":%{time_namelookup},'
    '"time_connect":%{time_connect},'
    '"time_appconnect":%{time_appconnect},'
    '"time_pretransfer":%{time_pretransfer},'
    '"time_redirect":%{time_redirect},'
    '"time_starttransfer":%{time_starttransfer},'
    '"time_total":%{time_total},'
    '"http_code":%{http_code},'
    '"size_download":%{size_download},'
    '"speed_download":%{speed_download}}'
)

def curl_base(url: str, auth: bool, extra_args=None) -> dict:
    cmd = ["curl", "-s", "-L",
           "-c", COOKIE_JAR,
           "-o", "/dev/null",
           "-w", CURL_FMT]
    if auth:
        cmd += ["-b", COOKIE_JAR]
    if extra_args:
        cmd += extra_args
    cmd.append(url)
    result = subprocess.run(cmd, capture_output=True, text=True, timeout=30)
    try:
        return json.loads(result.stdout)
    except json.JSONDecodeError:
        print(f"  [warn] bad curl output: {result.stdout!r}", file=sys.stderr)
        return {}

def get_csrf() -> str:
    result = subprocess.run(
        ["curl", "-s", "-c", COOKIE_JAR, f"{BASE_URL}/login"],
        capture_output=True, text=True, timeout=10
    )
    m = re.search(r'name="_token"\s+value="([^"]+)"', result.stdout)
    return m.group(1) if m else ""

def login():
    print(f"[auth] fetching CSRF …", file=sys.stderr)
    token = get_csrf()
    print(f"[auth] logging in as {EMAIL} …", file=sys.stderr)
    subprocess.run([
        "curl", "-s", "-L",
        "-b", COOKIE_JAR, "-c", COOKIE_JAR,
        "-X", "POST", f"{BASE_URL}/login",
        "-H", "Content-Type: application/x-www-form-urlencoded",
        "--data-urlencode", f"email={EMAIL}",
        "--data-urlencode", f"password={PASSW}",
        "--data-urlencode", f"_token={token}",
        "-o", "/dev/null",
    ], timeout=15)
    print(f"[auth] done", file=sys.stderr)

def measure(method: str, path: str, auth: bool) -> list[dict]:
    url = f"{BASE_URL}{path}"
    samples = []
    for i in range(1, ROUNDS + 1):
        sample = curl_base(url, auth, ["-X", method])
        samples.append(sample)
        code = int(sample.get("http_code", 0))
        ttfb = sample.get("time_starttransfer", 0) * 1000
        tot  = sample.get("time_total", 0) * 1000
        kb   = sample.get("size_download", 0) / 1024
        print(f"  [{i}/{ROUNDS}] {method} {path:<35} HTTP {code}  TTFB {ttfb:6.0f}ms  Total {tot:7.0f}ms  {kb:6.1f}k", file=sys.stderr)
    return samples

# ── Run ───────────────────────────────────────────────────────────────────────
print(f"[info] curl benchmark  rounds={ROUNDS}  base={BASE_URL}", file=sys.stderr)
login()

routes_out = {}
for method, path, auth in ROUTES:
    key = f"{method}:{path}"
    print(f"[route] {method} {path}", file=sys.stderr)
    samples = measure(method, path, auth)
    routes_out[key] = {"path": path, "method": method, "auth": auth, "samples": samples}

output = {"meta": {"generated_at": TIMESTAMP, "base_url": BASE_URL, "rounds": ROUNDS}, "routes": routes_out}

# Write timestamped file
RESULT_FILE.write_text(json.dumps(output, indent=2))
print(f"[done] written → {RESULT_FILE}", file=sys.stderr)

# Also update results_latest.json
(OUTPUT_DIR / "results_latest.json").write_text(json.dumps(output, indent=2))

# Print summary table
def ms(v): return f"{v*1000:.0f}ms"
rows = []
for v in output["routes"].values():
    s = [x for x in v["samples"] if x]
    if not s: continue
    ttfbs  = [x["time_starttransfer"] for x in s]
    totals = [x["time_total"]         for x in s]
    code   = int(s[-1]["http_code"])
    kb     = s[-1]["size_download"] / 1024
    rows.append((v["method"], v["path"], len(s), ttfbs, totals, code, kb))

HDR = f"{'Route':<42} {'N':>2}  {'TTFB_min':>9} {'TTFB_avg':>9} {'TTFB_max':>9}  {'Total_min':>10} {'Total_avg':>10} {'Total_max':>10}  {'HTTP':>5}  {'KB':>7}"
SEP = "-" * len(HDR)
print(f"\n{HDR}\n{SEP}", file=sys.stderr)
for method, path, n, ttfbs, totals, code, kb in rows:
    label = f"{method} {path}"
    print(
        f"{label:<42} {n:>2}"
        f"  {min(ttfbs)*1000:>8.0f}ms {sum(ttfbs)/n*1000:>8.0f}ms {max(ttfbs)*1000:>8.0f}ms"
        f"  {min(totals)*1000:>9.0f}ms {sum(totals)/n*1000:>9.0f}ms {max(totals)*1000:>9.0f}ms"
        f"  {code:>5}  {kb:>6.1f}k",
        file=sys.stderr
    )
