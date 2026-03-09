# Curl Test Suite Commands (2026-02-17)

## Running the Suites

```bash
cd _inc/laravel

# Prerequisites: start the dev server
php artisan serve --port=8000 &

# ── Run all test suites ──
bash tests/sh/run_all.sh

# ── Run specific suites by number prefix ──
bash tests/sh/run_all.sh 00 01 02    # auth + GET + POST only
bash tests/sh/run_all.sh 06          # timing report only
bash tests/sh/run_all.sh 09 10       # unauth + CSRF checks

# ── Run individual scripts directly ──
bash tests/sh/00_auth.sh
bash tests/sh/01_get_routes.sh
bash tests/sh/06_timing_report.sh
bash tests/sh/07_stress_quick.sh 8 5   # 8 concurrent × 5 reps

# ── Health monitoring with interval ──
bash tests/sh/05_watch_health.sh 3 50   # every 3s, 50 cycles
```

## Configuration via Environment Variables

```bash
# Base URL (default: http://127.0.0.1:8000)
export ERP_BASE_URL="http://127.0.0.1:8000"

# Credentials (default: admin@admin.com / admin)
export ERP_TEST_EMAIL="suporte@prestech.com.br"
export ERP_TEST_PASS="yourpassword"

# Timeouts (seconds)
export CURL_TIMEOUT=30
export CURL_CONNECT_TIMEOUT=10
export CURL_MAX_RETRIES=2

# Verbose output (show response body on failure)
export VERBOSE=1

# Log directory (default: /tmp/erp_curl_logs)
export LOG_DIR="/tmp/erp_curl_logs"

# Cookie jar (default: /tmp/erp_curl_cookies.txt)
export COOKIE_JAR="/tmp/erp_curl_cookies.txt"
```

## Output Files

| File | Content |
|------|---------|
| `/tmp/erp_curl_logs/timing.csv` | method,uri,http_code,timestamp |
| `/tmp/erp_curl_logs/full_timing.csv` | Detailed timing per route |
| `/tmp/erp_curl_logs/health_watch.csv` | Periodic health check results |
| `/tmp/erp_curl_logs/stress_results.csv` | Concurrency test results |

## Re-generating Tests

If routes change, re-generate from the route list:

```bash
cd _inc/laravel

# Export fresh route JSON
php artisan route:list --json 2>/dev/null | grep -v '^[ ]*Constructing' > /tmp/erp_routes.json

# Run the generator
python3 tests/sh/_generate_curl_tests.py

# Verify
ls -la tests/sh/*.sh | wc -l   # should be 15
wc -l tests/sh/*.sh            # total lines
```

## Suite Summary

| # | Script | Tests | Description |
|---|--------|-------|-------------|
| 00 | auth | ~10 | Login, logout, session validation, invalid creds |
| 01 | get_routes | 846 | Every GET endpoint, grouped by category |
| 02 | post_routes | 409 | Every POST with CSRF token |
| 03 | put_patch_routes | 298 | Every PUT/PATCH with CSRF |
| 04 | delete_routes | 188 | Every DELETE with CSRF |
| 05 | watch_health | 15×N | Periodic health monitoring (configurable interval/count) |
| 06 | timing_report | 1,515 | Full `-w` timing for every route, top-20 slowest |
| 07 | stress_quick | 20×P×R | xargs parallel concurrency per endpoint |
| 08 | header_variants | 40×4 | JSON, AJAX, both, plain — per sampled route |
| 09 | unauthenticated | 60 | Verify auth guards reject unsigned requests |
| 10 | csrf_validation | 30 | Verify CSRF rejection on POST without token |
| 11 | json_api | 2 | API/ prefixed routes with JSON headers |
| 12 | landing_page | 7 | LandingPage module routes |
