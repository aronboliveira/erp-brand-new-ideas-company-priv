#!/usr/bin/env python3
"""
Deep test for the 15 still-failing routes:
 - 5× 429 rate-limited  (pricing_plans, screenshots, testimonials)
 - 1× 302 redirect      (/landingpage/create)
 - 1× 403 permission    (/projects.timesheets/table-view)
 - 8× 404 installer     (/install, /installs/*)
Checks root cause, headers, redirect chain, error bodies.
Includes hardware-based throttling: sleeps when CPU/temp too high.
"""
import subprocess, os, sys, time, json, re
from datetime import datetime
from pathlib import Path

BASE_URL   = os.environ.get("SMOKE_TEST_URL", "http://127.0.0.1:8888")
COOKIE_FILE = "/tmp/deep_test_cookies.txt"
ADMIN_EMAIL = os.environ.get("SMOKE_TEST_EMAIL",    "alexys87@example.org")
ADMIN_PASS  = os.environ.get("SMOKE_TEST_PASSWORD", "Admin@1234")
RESULTS_DIR = os.path.dirname(os.path.abspath(__file__)) + "/../"

CPU_LIMIT   = 150  # % – pause threshold (×3 in hw_ok = 450% total; VS Code+PHP-LS baseline ~250%)
TEMP_LIMIT  = 87   # °C – pause threshold
SLEEP_COOL  = 20   # seconds to sleep when hot

# ---------------------------------------------------------------------------
# Hardware monitor
# ---------------------------------------------------------------------------

def hw_ok() -> tuple[bool, dict]:
    """Returns (ok, metrics). ok=False means we must sleep."""
    m = {"cpu": 0.0, "mem_pct": 0.0, "temp_max": 0.0}
    try:
        ps = subprocess.run(
            ["ps", "-A", "-o", "%cpu"],
            capture_output=True, text=True, timeout=3
        )
        vals = [float(x) for x in ps.stdout.split() if x.replace('.','').isdigit()]
        m["cpu"] = sum(vals)
    except Exception:
        pass
    try:
        fm = subprocess.run(["free", "-m"], capture_output=True, text=True, timeout=3)
        for line in fm.stdout.splitlines():
            if line.startswith("Mem:"):
                parts = line.split()
                total, used = float(parts[1]), float(parts[2])
                m["mem_pct"] = (used / total * 100) if total else 0
    except Exception:
        pass
    try:
        sens = subprocess.run(["sensors"], capture_output=True, text=True, timeout=5)
        # Only pick the actual reading (after ':'), not high/crit thresholds in parens
        temps = re.findall(r':\s+\+(\d+\.\d+)°C', sens.stdout)
        if temps:
            m["temp_max"] = max(float(t) for t in temps)
    except Exception:
        pass
    ok = (m["cpu"] < CPU_LIMIT * 3 and   # ps -A gives all procs, multiply threshold
          m["temp_max"] < TEMP_LIMIT)
    return ok, m


def wait_for_cool(label=""):
    while True:
        ok, m = hw_ok()
        if ok:
            return
        print(f"  ⏸  [{label}] CPU={m['cpu']:.0f}% MEM={m['mem_pct']:.0f}% "
              f"T={m['temp_max']:.0f}°C — sleeping {SLEEP_COOL}s …")
        time.sleep(SLEEP_COOL)

# ---------------------------------------------------------------------------
# Auth helpers
# ---------------------------------------------------------------------------

def _curl(*args, timeout=15):
    try:
        r = subprocess.run(
            ["curl", "-s", "--max-time", str(timeout)] + list(args),
            capture_output=True, text=True, timeout=timeout + 5
        )
        return r.stdout, r.returncode
    except subprocess.TimeoutExpired:
        return "", -1


def login():
    os.makedirs("/tmp", exist_ok=True)
    # Get CSRF
    body, _ = _curl("-c", COOKIE_FILE, f"{BASE_URL}/login")
    m = re.search(r'name="_token"\s+value="([^"]+)"', body)
    if not m:
        m = re.search(r'meta name="csrf-token" content="([^"]+)"', body)
    csrf = m.group(1) if m else ""
    # POST login
    _curl(
        "-b", COOKIE_FILE, "-c", COOKIE_FILE,
        "-X", "POST",
        "-H", "Content-Type: application/x-www-form-urlencoded",
        "-d", f"_token={csrf}&email={ADMIN_EMAIL}&password={ADMIN_PASS}",
        f"{BASE_URL}/login"
    )
    print(f"✓ auth as {ADMIN_EMAIL}")


# ---------------------------------------------------------------------------
# Route tester – verbose
# ---------------------------------------------------------------------------

def test_route(uri, label=""):
    url = f"{BASE_URL}/{uri.lstrip('/')}"
    # Full verbose: dump headers + body snippet
    body, _ = _curl(
        "-b", COOKIE_FILE,
        "-D", "-",           # include headers in output
        "-L",                # follow redirects (up to 10)
        "--max-redirs", "5",
        url, timeout=20
    )

    # Parse the first HTTP status line
    status_lines = re.findall(r'^HTTP/\S+\s+(\d+)', body, re.MULTILINE)
    final_status = int(status_lines[-1]) if status_lines else 0

    # Collect redirect chain
    locations = re.findall(r'^[Ll]ocation:\s*(\S+)', body, re.MULTILINE)

    # Extract response body (after last blank line following headers)
    parts = re.split(r'\r?\n\r?\n', body)
    html_body = parts[-1] if len(parts) > 1 else ""

    # Rate-limit headers
    retry_after = re.search(r'[Rr]etry-[Aa]fter:\s*(\d+)', body)
    x_ratelimit = re.search(r'X-RateLimit-Limit:\s*(\d+)', body)

    result = {
        "uri":         uri,
        "url":         url,
        "final_status":final_status,
        "status_chain":status_lines,
        "redirects":   locations,
        "retry_after": retry_after.group(1) if retry_after else None,
        "x_ratelimit": x_ratelimit.group(1) if x_ratelimit else None,
        "body_len":    len(html_body),
        "body_snippet":html_body[:500].replace('\n',' ')[:300],
        "label":       label,
    }

    icon = "✓" if final_status < 299 else "⚠" if final_status < 500 else "✗"
    print(f"  {icon} [{final_status}] {uri}")
    if locations:
        print(f"      redirects: {' → '.join(locations)}")
    if retry_after:
        print(f"      retry-after: {retry_after.group(1)}s")
    return result


# ---------------------------------------------------------------------------
# Rate-limit bypass: clear throttle via artisan cache
# ---------------------------------------------------------------------------

LARAVEL_DIR = str(Path(__file__).resolve().parents[3])

def clear_rate_limits():
    """Clear Laravel rate-limit entries from the cache driver."""
    print("  🔄 Clearing rate-limit cache …")
    try:
        r = subprocess.run(
            ["php", "artisan", "cache:clear"],
            capture_output=True, text=True, cwd=LARAVEL_DIR, timeout=15
        )
        return "cleared" in r.stdout.lower() or r.returncode == 0
    except Exception as e:
        print(f"  ! cache:clear failed: {e}")
        return False


# ---------------------------------------------------------------------------
# MAIN
# ---------------------------------------------------------------------------

FAILING = {
    "rate_limited_429": [
        "/pricing_plans/create",
        "/screenshots",
        "/screenshots/create",
        "/testimonials",
        "/testimonials/create",
    ],
    "redirect_302": [
        "/landingpage/create",
    ],
    "permission_403": [
        "/projects.timesheets/table-view",
    ],
    "not_found_404": [
        "/install",
        "/installs/database",
        "/installs/environment",
        "/installs/environments/classic",
        "/installs/environments/wizard",
        "/installs/final",
        "/installs/permissions",
        "/installs/requirements",
    ],
}

def main():
    print("=" * 72)
    print("Deep Failure Analysis")
    print(f"Base URL: {BASE_URL}")
    print(f"Started:  {datetime.now().isoformat()}")
    print("=" * 72)

    wait_for_cool("startup")
    login()
    time.sleep(1)

    results = {}

    # ── 1. 429 rate-limited ────────────────────────────────────────────────
    print("\n── Section 1: 429 Rate-Limited Routes ──")
    results["rate_limited_first_pass"] = []
    for uri in FAILING["rate_limited_429"]:
        wait_for_cool(uri)
        r = test_route(uri, "429-first-pass")
        results["rate_limited_first_pass"].append(r)
        time.sleep(1)

    # Try again after clearing cache
    print("\n  → Clearing cache and retrying …")
    time.sleep(2)
    clear_rate_limits()
    time.sleep(3)
    login()   # refresh session after cache clear

    results["rate_limited_after_clear"] = []
    for uri in FAILING["rate_limited_429"]:
        wait_for_cool(uri)
        r = test_route(uri, "429-after-clear")
        results["rate_limited_after_clear"].append(r)
        time.sleep(1.5)

    # Get rate-limit config from code
    print("\n  → Checking rate-limit config …")
    rl_grep = subprocess.run(
        ["grep", "-rn", r"throttle\|RateLimiter\|pricing_plans\|screenshots\|testimonials",
         "app/Http/Kernel.php", "routes/web.php", "app/Providers/RouteServiceProvider.php"],
        capture_output=True, text=True, cwd=LARAVEL_DIR
    )
    results["rate_limit_config_grep"] = rl_grep.stdout[:2000]

    # ── 2. 302 Redirects ──────────────────────────────────────────────────
    print("\n── Section 2: 302 Redirect Routes ──")
    results["redirect_analysis"] = []
    for uri in FAILING["redirect_302"]:
        wait_for_cool(uri)
        r = test_route(uri, "302")
        results["redirect_analysis"].append(r)
        time.sleep(1)

    # Find the LandingPage controller
    lp_ctrl = subprocess.run(
        ["grep", "-rn", "function create", "Modules/LandingPage/"],
        capture_output=True, text=True, cwd=LARAVEL_DIR
    )
    results["landingpage_create_grep"] = lp_ctrl.stdout[:1000]

    # ── 3. 403 Permission ─────────────────────────────────────────────────
    print("\n── Section 3: 403 Permission Routes ──")
    results["permission_analysis"] = []
    for uri in FAILING["permission_403"]:
        wait_for_cool(uri)
        r = test_route(uri, "403")
        results["permission_analysis"].append(r)
        time.sleep(1)

    # Try with other user types
    print("  → Checking timesheets permission requirements …")
    ts_grep = subprocess.run(
        ["grep", "-rn", r"table-view\|tableView\|timesheets.*permission\|PERM.*TIME",
         "app/", "routes/"],
        capture_output=True, text=True, cwd=LARAVEL_DIR
    )
    results["timesheets_permission_grep"] = ts_grep.stdout[:2000]

    # ── 4. 404 Installer ─────────────────────────────────────────────────
    print("\n── Section 4: 404 Installer Routes ──")
    results["not_found_analysis"] = []
    for uri in FAILING["not_found_404"]:
        wait_for_cool(uri)
        r = test_route(uri, "404")
        results["not_found_analysis"].append(r)
        time.sleep(0.5)

    # Check installer module status
    modules_json = LARAVEL_DIR + "/modules_statuses.json"
    if os.path.exists(modules_json):
        with open(modules_json) as f:
            results["modules_statuses"] = json.load(f)

    # ── Save results ──────────────────────────────────────────────────────
    out_path = RESULTS_DIR + "deep_test_failing_results.json"
    with open(out_path, "w") as f:
        json.dump(results, f, indent=2, default=str)
    print(f"\n✓ Results saved to: {out_path}")

    # ── Print summary ─────────────────────────────────────────────────────
    print("\n" + "=" * 72)
    print("DEEP TEST SUMMARY")
    print("=" * 72)
    for uri in FAILING["rate_limited_429"]:
        row_a = next((r for r in results["rate_limited_first_pass"]  if r["uri"] == uri), {})
        row_b = next((r for r in results["rate_limited_after_clear"] if r["uri"] == uri), {})
        print(f"  429 {uri}: before={row_a.get('final_status','?')} after_clear={row_b.get('final_status','?')} retry={row_a.get('retry_after','?')}s")
    for r in results["redirect_analysis"]:
        print(f"  302 {r['uri']}: final={r['final_status']} redirects={r['redirects']}")
    for r in results["permission_analysis"]:
        print(f"  403 {r['uri']}: final={r['final_status']} snippet={r['body_snippet'][:80]}")
    for r in results["not_found_analysis"]:
        print(f"  404 {r['uri']}: final={r['final_status']} snippet={r['body_snippet'][:60]}")


if __name__ == "__main__":
    main()
