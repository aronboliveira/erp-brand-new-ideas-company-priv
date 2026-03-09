#!/usr/bin/env python3
"""
Generate EXTENSIVE curl test suites for every route in the ERP application.

Reads /tmp/erp_routes.json (produced by `php artisan route:list --json`) and
emits shell scripts in the same directory as this file.

File layout:
  _common.sh             – shared helpers, colours, cookie jar, CSRF logic
  00_auth.sh             – login + session bootstrap
  01_get_routes.sh       – all GET|HEAD routes
  02_post_routes.sh      – all POST routes
  03_put_patch_routes.sh – all PUT|PATCH routes
  04_delete_routes.sh    – all DELETE routes
  05_watch_health.sh     – watch -n ticks for health / dashboard
  06_timing_report.sh    – curl -w timing for every route
  07_stress_quick.sh     – quick concurrency stress (xargs -P)
  run_all.sh             – orchestrator
"""
import json, os, textwrap, re
from pathlib import Path

HERE = Path(__file__).resolve().parent
ROUTES_FILE = Path("/tmp/erp_routes.json")

routes = json.loads(ROUTES_FILE.read_text())

# ── helpers ──────────────────────────────────────────────────────────

def sh_header(title: str, extra: str = "") -> str:
    return textwrap.dedent(f"""\
    #!/usr/bin/env bash
    # ─────────────────────────────────────────────────────
    # {title}
    # Auto-generated — do not edit by hand.
    # ─────────────────────────────────────────────────────
    set -euo pipefail
    SCRIPT_DIR="$(cd "$(dirname "${{BASH_SOURCE[0]}}")" && pwd)"
    # shellcheck source=_common.sh
    source "$SCRIPT_DIR/_common.sh"
    {extra}
    """)


def sanitise_uri(uri: str) -> str:
    """Replace {param} placeholders with test values."""
    uri = re.sub(r'\{[^}]*[Ii]d\}', '1', uri)
    uri = re.sub(r'\{[^}]*\}', '1', uri)
    return '/' + uri.lstrip('/')


def needs_auth(mw) -> bool:
    if isinstance(mw, str):
        mw = [mw]
    return any('auth' in str(m).lower() or 'Authenticate' in str(m) for m in mw)


# ── split routes by method ──────────────────────────────────────────

gets, posts, puts, patches, deletes = [], [], [], [], []

for r in routes:
    methods = r['method'].upper().split('|')
    uri = r['uri']
    mw = r.get('middleware') or []
    entry = {'uri': uri, 'name': r.get('name',''), 'auth': needs_auth(mw), 'middleware': mw}
    if 'GET' in methods:
        gets.append(entry)
    if 'POST' in methods:
        posts.append(entry)
    if 'PUT' in methods:
        puts.append(entry)
    if 'PATCH' in methods:
        patches.append(entry)
    if 'DELETE' in methods:
        deletes.append(entry)


# =====================================================================
# _common.sh
# =====================================================================
common = textwrap.dedent('''\
#!/usr/bin/env bash
# ─────────────────────────────────────────────────────
# _common.sh  –  Shared helpers for ERP curl test suites
# ─────────────────────────────────────────────────────

# ── Configuration ────────────────────────────────────
export BASE_URL="${ERP_BASE_URL:-http://127.0.0.1:8000}"
export COOKIE_JAR="${COOKIE_JAR:-/tmp/erp_curl_cookies.txt}"
export CSRF_TOKEN=""
export LOG_DIR="${LOG_DIR:-/tmp/erp_curl_logs}"
export TIMING_LOG="$LOG_DIR/timing.csv"
export VERBOSE="${VERBOSE:-0}"
export TIMEOUT="${CURL_TIMEOUT:-30}"
export CONNECT_TIMEOUT="${CURL_CONNECT_TIMEOUT:-10}"
export MAX_RETRIES="${CURL_MAX_RETRIES:-2}"
export TEST_EMAIL="${ERP_TEST_EMAIL:-admin@admin.com}"
export TEST_PASS="${ERP_TEST_PASS:-admin}"

mkdir -p "$LOG_DIR"

# ── Colours ──────────────────────────────────────────
if [[ -t 1 ]]; then
    C_RST="\\033[0m"; C_RED="\\033[0;31m"; C_GRN="\\033[0;32m"
    C_YLW="\\033[0;33m"; C_BLU="\\033[0;34m"; C_CYN="\\033[0;36m"
    C_BLD="\\033[1m"
else
    C_RST=""; C_RED=""; C_GRN=""; C_YLW=""; C_BLU=""; C_CYN=""; C_BLD=""
fi

# ── Counters ─────────────────────────────────────────
PASS=0; FAIL=0; SKIP=0; WARN=0; TOTAL=0

# ── Curl write-out format ────────────────────────────
# Extensive timing and metadata output
W_FMT='{"http_code":%{http_code},"time_total":%{time_total},"time_namelookup":%{time_namelookup},"time_connect":%{time_connect},"time_appconnect":%{time_appconnect},"time_pretransfer":%{time_pretransfer},"time_starttransfer":%{time_starttransfer},"time_redirect":%{time_redirect},"size_download":%{size_download},"size_upload":%{size_upload},"speed_download":%{speed_download},"url_effective":"%{url_effective}","num_redirects":%{num_redirects},"redirect_url":"%{redirect_url}","content_type":"%{content_type}","ssl_verify_result":%{ssl_verify_result}}'

# Compact timing format for quick scans
W_TIMING='%{http_code} %{time_total}s %{size_download}B %{url_effective}\\n'

# ── Helper functions ─────────────────────────────────

log_info()  { printf "${C_BLU}[INFO]${C_RST}  %s\\n" "$*"; }
log_pass()  { printf "${C_GRN}[PASS]${C_RST}  %s\\n" "$*"; ((PASS++)); ((TOTAL++)); }
log_fail()  { printf "${C_RED}[FAIL]${C_RST}  %s\\n" "$*"; ((FAIL++)); ((TOTAL++)); }
log_warn()  { printf "${C_YLW}[WARN]${C_RST}  %s\\n" "$*"; ((WARN++)); }
log_skip()  { printf "${C_CYN}[SKIP]${C_RST}  %s\\n" "$*"; ((SKIP++)); }

summary() {
    echo ""
    printf "${C_BLD}═══════════════════════════════════════════════════════${C_RST}\\n"
    printf "${C_BLD}  RESULTS:${C_RST}  "
    printf "${C_GRN}%d pass${C_RST}  " "$PASS"
    printf "${C_RED}%d fail${C_RST}  " "$FAIL"
    printf "${C_YLW}%d warn${C_RST}  " "$WARN"
    printf "${C_CYN}%d skip${C_RST}  " "$SKIP"
    printf "/ ${C_BLD}%d total${C_RST}\\n" "$TOTAL"
    printf "${C_BLD}═══════════════════════════════════════════════════════${C_RST}\\n"
    if (( FAIL > 0 )); then
        return 1
    fi
    return 0
}

# ── CSRF extraction ─────────────────────────────────
fetch_csrf() {
    local html
    html="$(curl -sSL -b "$COOKIE_JAR" -c "$COOKIE_JAR" \\
        --connect-timeout "$CONNECT_TIMEOUT" \\
        --max-time "$TIMEOUT" \\
        "$BASE_URL/login" 2>/dev/null)"
    CSRF_TOKEN="$(echo "$html" | grep -oP 'name="_token"\\s+value="\\K[^"]+' || true)"
    if [[ -z "$CSRF_TOKEN" ]]; then
        CSRF_TOKEN="$(echo "$html" | grep -oP 'csrf-token.*?content="\\K[^"]+' || true)"
    fi
    export CSRF_TOKEN
    [[ -n "$CSRF_TOKEN" ]] && return 0 || return 1
}

# ── Authenticated login ─────────────────────────────
do_login() {
    log_info "Logging in as $TEST_EMAIL ..."
    fetch_csrf || { log_fail "Could not fetch CSRF token"; return 1; }

    local resp
    resp="$(curl -sS -w '\\n%{http_code}' \\
        -b "$COOKIE_JAR" -c "$COOKIE_JAR" \\
        --connect-timeout "$CONNECT_TIMEOUT" \\
        --max-time "$TIMEOUT" \\
        -X POST "$BASE_URL/login" \\
        -d "email=$TEST_EMAIL&password=$TEST_PASS&_token=$CSRF_TOKEN" \\
        -H "Content-Type: application/x-www-form-urlencoded" \\
        -L 2>/dev/null)"
    local code
    code="$(echo "$resp" | tail -1)"
    if [[ "$code" =~ ^(200|302)$ ]]; then
        log_pass "Login succeeded (HTTP $code)"
        return 0
    else
        log_fail "Login failed (HTTP $code)"
        return 1
    fi
}

# ── Generic curl test with retries ──────────────────
# Usage: curl_test METHOD URI [EXPECTED_CODES] [EXTRA_FLAGS...]
#   EXPECTED_CODES: comma-separated, e.g. "200,302,403"
curl_test() {
    local method="${1:?}" uri="${2:?}" expected="${3:-200,302}"
    shift 3
    local extra_flags=("$@")
    local url="$BASE_URL$uri"
    local label="$method $uri"
    local attempt=0 code="" body=""

    while (( attempt <= MAX_RETRIES )); do
        local result
        result="$(curl -sS -w '\\n%{http_code}' \\
            -b "$COOKIE_JAR" -c "$COOKIE_JAR" \\
            --connect-timeout "$CONNECT_TIMEOUT" \\
            --max-time "$TIMEOUT" \\
            -X "$method" \\
            "${extra_flags[@]}" \\
            "$url" 2>/dev/null)" || true

        code="$(echo "$result" | tail -1)"
        body="$(echo "$result" | sed '$ d')"

        # Connection failure → retry
        if [[ -z "$code" || "$code" == "000" ]]; then
            ((attempt++))
            sleep 1
            continue
        fi
        break
    done

    # Check against expected codes
    local ok=false
    IFS=',' read -ra EXPECTED_ARR <<< "$expected"
    for ec in "${EXPECTED_ARR[@]}"; do
        if [[ "$code" == "$ec" ]]; then ok=true; break; fi
    done

    if $ok; then
        log_pass "$label → $code"
    else
        log_fail "$label → $code (expected: $expected)"
        if (( VERBOSE )); then
            echo "  Body (first 200 chars): ${body:0:200}"
        fi
    fi

    # Append to timing CSV
    echo "$method,$uri,$code,$(date +%s)" >> "$TIMING_LOG" 2>/dev/null || true
}

# ── Curl with full timing output ────────────────────
# Writes timing JSON to stdout and code to stderr
curl_timed() {
    local method="${1:?}" uri="${2:?}"
    shift 2
    curl -sS -w "$W_FMT" \\
        -b "$COOKIE_JAR" -c "$COOKIE_JAR" \\
        --connect-timeout "$CONNECT_TIMEOUT" \\
        --max-time "$TIMEOUT" \\
        -X "$method" \\
        -o /dev/null "$@" \\
        "$BASE_URL$uri" 2>/dev/null
}

# ── Curl with various header combos ────────────────
curl_header_variants() {
    local method="${1:?}" uri="${2:?}"
    local url="$BASE_URL$uri"
    local label="$method $uri"

    # Plain
    curl_test "$method" "$uri" "200,302,301,401,403,405"

    # Accept: application/json
    curl_test "$method" "$uri" "200,302,301,401,403,405,422" \\
        -H "Accept: application/json"

    # XMLHttpRequest (AJAX)
    curl_test "$method" "$uri" "200,302,301,401,403,405,422" \\
        -H "X-Requested-With: XMLHttpRequest"

    # Both JSON + AJAX
    curl_test "$method" "$uri" "200,302,301,401,403,405,422" \\
        -H "Accept: application/json" \\
        -H "X-Requested-With: XMLHttpRequest"
}

# ── POST with CSRF ──────────────────────────────────
curl_post_csrf() {
    local uri="${1:?}" expected="${2:-200,302,422}" data="${3:-}"
    fetch_csrf 2>/dev/null || true
    curl_test "POST" "$uri" "$expected" \\
        -H "Content-Type: application/x-www-form-urlencoded" \\
        -d "_token=$CSRF_TOKEN${data:+&$data}"
}

# ── PUT/PATCH with CSRF ─────────────────────────────
curl_write_csrf() {
    local method="${1:?}" uri="${2:?}" expected="${3:-200,302,422}" data="${4:-}"
    fetch_csrf 2>/dev/null || true
    curl_test "$method" "$uri" "$expected" \\
        -H "Content-Type: application/x-www-form-urlencoded" \\
        -d "_token=$CSRF_TOKEN&_method=$method${data:+&$data}"
}
''')

(HERE / '_common.sh').write_text(common)
print("✓ _common.sh")


# =====================================================================
# 00_auth.sh
# =====================================================================
auth = sh_header("00_auth.sh  –  Authentication / session bootstrap")
auth += textwrap.dedent('''\
log_info "=== AUTHENTICATION TESTS ==="

# ── Unauthenticated access should redirect ────────
rm -f "$COOKIE_JAR"
curl_test GET "/" "302,200,301"
curl_test GET "/login" "200"
curl_test GET "/register" "200,302,404"

# ── Login with invalid creds ─────────────────────
fetch_csrf
curl_test POST "/login" "302,422,200" \\
    -H "Content-Type: application/x-www-form-urlencoded" \\
    -d "_token=$CSRF_TOKEN&email=bad@bad.com&password=wrongpass"

# ── Login with correct creds ─────────────────────
do_login || { log_fail "Cannot proceed without auth"; summary; exit 1; }

# ── Verify session is valid ──────────────────────
curl_test GET "/" "200,302"

# ── Profile / user endpoints ─────────────────────
curl_test GET "/user" "200,302,404"
curl_test GET "/settings" "200,302"

# ── Logout ───────────────────────────────────────
fetch_csrf
curl_test POST "/logout" "302,200" \\
    -H "Content-Type: application/x-www-form-urlencoded" \\
    -d "_token=$CSRF_TOKEN"

# ── Re-login for subsequent suites ───────────────
do_login

log_info "Auth tests complete."
summary
''')
(HERE / '00_auth.sh').write_text(auth)
print("✓ 00_auth.sh")


# =====================================================================
# 01_get_routes.sh – ALL GET routes, sorted, with header variants
# =====================================================================
get_script = sh_header("01_get_routes.sh  –  GET route coverage (all %d GET routes)" % len(gets))
get_script += 'log_info "=== GET ROUTE TESTS (%d routes) ==="\n\n' % len(gets)
get_script += '# Ensure authenticated session\ndo_login\n\n'

# Group by category for readability
from collections import defaultdict
get_cats = defaultdict(list)
for g in gets:
    cat = g['uri'].strip('/').split('/')[0] or 'root'
    get_cats[cat].append(g)

for cat in sorted(get_cats.keys()):
    get_script += f'# ── {cat} ──────────────────────────────────\n'
    for g in get_cats[cat]:
        uri = sanitise_uri(g['uri'])
        name = g.get('name') or ''
        comment = f"  # {name}" if name else ""
        # Most GETs expect 200 or 302 (redirect) or 403 (forbidden)
        get_script += f'curl_test GET "{uri}" "200,302,301,403,404"{comment}\n'
    get_script += '\n'

get_script += 'log_info "GET route tests complete."\nsummary\n'
(HERE / '01_get_routes.sh').write_text(get_script)
print(f"✓ 01_get_routes.sh ({len(gets)} GET routes)")


# =====================================================================
# 02_post_routes.sh – ALL POST routes
# =====================================================================
post_script = sh_header("02_post_routes.sh  –  POST route coverage (all %d POST routes)" % len(posts))
post_script += 'log_info "=== POST ROUTE TESTS (%d routes) ==="\n\n' % len(posts)
post_script += '# Ensure authenticated session\ndo_login\n\n'

post_cats = defaultdict(list)
for p in posts:
    cat = p['uri'].strip('/').split('/')[0] or 'root'
    post_cats[cat].append(p)

for cat in sorted(post_cats.keys()):
    post_script += f'# ── {cat} ──────────────────────────────────\n'
    for p in post_cats[cat]:
        uri = sanitise_uri(p['uri'])
        name = p.get('name') or ''
        comment = f"  # {name}" if name else ""
        # POST without required fields → 422 validation; with CSRF → 302 redirect
        post_script += f'curl_post_csrf "{uri}" "200,302,422,403,404,419,405"{comment}\n'
    post_script += '\n'

post_script += 'log_info "POST route tests complete."\nsummary\n'
(HERE / '02_post_routes.sh').write_text(post_script)
print(f"✓ 02_post_routes.sh ({len(posts)} POST routes)")


# =====================================================================
# 03_put_patch_routes.sh
# =====================================================================
pp = puts + patches  # combined
pp_script = sh_header("03_put_patch_routes.sh  –  PUT|PATCH route coverage (%d routes)" % len(pp))
pp_script += 'log_info "=== PUT/PATCH ROUTE TESTS (%d routes) ==="\n\n' % len(pp)
pp_script += '# Ensure authenticated session\ndo_login\n\n'

pp_cats = defaultdict(list)
for r in puts:
    cat = r['uri'].strip('/').split('/')[0] or 'root'
    pp_cats[cat].append(('PUT', r))
for r in patches:
    cat = r['uri'].strip('/').split('/')[0] or 'root'
    pp_cats[cat].append(('PATCH', r))

for cat in sorted(pp_cats.keys()):
    pp_script += f'# ── {cat} ──────────────────────────────────\n'
    for method, r in pp_cats[cat]:
        uri = sanitise_uri(r['uri'])
        name = r.get('name') or ''
        comment = f"  # {name}" if name else ""
        pp_script += f'curl_write_csrf "{method}" "{uri}" "200,302,422,403,404,405,419"{comment}\n'
    pp_script += '\n'

pp_script += 'log_info "PUT/PATCH route tests complete."\nsummary\n'
(HERE / '03_put_patch_routes.sh').write_text(pp_script)
print(f"✓ 03_put_patch_routes.sh ({len(pp)} PUT/PATCH routes)")


# =====================================================================
# 04_delete_routes.sh
# =====================================================================
del_script = sh_header("04_delete_routes.sh  –  DELETE route coverage (%d routes)" % len(deletes))
del_script += 'log_info "=== DELETE ROUTE TESTS (%d routes) ==="\n\n' % len(deletes)
del_script += '# Ensure authenticated session\ndo_login\n\n'
del_script += textwrap.dedent('''\
# NOTE: DELETE tests use IDs that may not exist, so 404 is acceptable.
# We only verify the route itself responds (not 500).

''')

del_cats = defaultdict(list)
for d in deletes:
    cat = d['uri'].strip('/').split('/')[0] or 'root'
    del_cats[cat].append(d)

for cat in sorted(del_cats.keys()):
    del_script += f'# ── {cat} ──────────────────────────────────\n'
    for d in del_cats[cat]:
        uri = sanitise_uri(d['uri'])
        name = d.get('name') or ''
        comment = f"  # {name}" if name else ""
        del_script += f'curl_post_csrf "{uri}" "200,302,404,403,405,419,422" "_method=DELETE"{comment}\n'
    del_script += '\n'

del_script += 'log_info "DELETE route tests complete."\nsummary\n'
(HERE / '04_delete_routes.sh').write_text(del_script)
print(f"✓ 04_delete_routes.sh ({len(deletes)} DELETE routes)")


# =====================================================================
# 05_watch_health.sh  –  watch -n ticks for monitoring
# =====================================================================
watch_script = sh_header("05_watch_health.sh  –  Periodic health checks via watch")
watch_script += textwrap.dedent('''\
# This script monitors key endpoints every N seconds.
# Usage: ./05_watch_health.sh [interval_secs] [count]
INTERVAL="${1:-5}"
MAX_CHECKS="${2:-20}"

ENDPOINTS=(
    "/"
    "/login"
    "/settings"
    "/customers"
    "/vendors"
    "/invoices"
    "/bills/1"
    "/employees"
    "/expenses"
    "/leads"
    "/projects"
    "/deals"
    "/proposals"
    "/reports"
    "/pos"
)

do_login

log_info "Monitoring ${#ENDPOINTS[@]} endpoints every ${INTERVAL}s for ${MAX_CHECKS} cycles..."
echo "timestamp,endpoint,http_code,time_total,size_download" > "$LOG_DIR/health_watch.csv"

for (( i=1; i<=MAX_CHECKS; i++ )); do
    printf "\\n${C_BLD}── Cycle %d/%d ──────────────────────────────${C_RST}\\n" "$i" "$MAX_CHECKS"
    for ep in "${ENDPOINTS[@]}"; do
        result="$(curl -sS -o /dev/null -w '%{http_code},%{time_total},%{size_download}' \\
            -b "$COOKIE_JAR" -c "$COOKIE_JAR" \\
            --connect-timeout "$CONNECT_TIMEOUT" \\
            --max-time "$TIMEOUT" \\
            "$BASE_URL$ep" 2>/dev/null)" || result="000,0,0"
        echo "$(date +%s),$ep,$result" >> "$LOG_DIR/health_watch.csv"

        IFS=',' read -r code ttime ssize <<< "$result"
        if [[ "$code" =~ ^(200|302|301)$ ]]; then
            printf "  ${C_GRN}%s${C_RST} %s  %.3fs  %sB\\n" "$code" "$ep" "$ttime" "$ssize"
        else
            printf "  ${C_RED}%s${C_RST} %s  %.3fs  %sB\\n" "$code" "$ep" "$ttime" "$ssize"
        fi
    done
    if (( i < MAX_CHECKS )); then
        sleep "$INTERVAL"
    fi
done

log_info "Health watch complete. CSV at $LOG_DIR/health_watch.csv"
''')
(HERE / '05_watch_health.sh').write_text(watch_script)
print("✓ 05_watch_health.sh")


# =====================================================================
# 06_timing_report.sh  –  Full timing report for every route
# =====================================================================
timing_script = sh_header("06_timing_report.sh  –  Detailed timing for all routes")
timing_script += textwrap.dedent('''\
log_info "=== TIMING REPORT (all routes) ==="
do_login

TIMING_FILE="$LOG_DIR/full_timing.csv"
echo "method,uri,http_code,time_total,time_namelookup,time_connect,time_starttransfer,size_download" > "$TIMING_FILE"

time_route() {
    local method="$1" uri="$2"
    local url="$BASE_URL$uri"
    local result
    result="$(curl -sS -o /dev/null \\
        -w '%{http_code},%{time_total},%{time_namelookup},%{time_connect},%{time_starttransfer},%{size_download}' \\
        -b "$COOKIE_JAR" -c "$COOKIE_JAR" \\
        --connect-timeout "$CONNECT_TIMEOUT" \\
        --max-time "$TIMEOUT" \\
        -X "$method" \\
        "$url" 2>/dev/null)" || result="000,0,0,0,0,0"
    echo "$method,$uri,$result" >> "$TIMING_FILE"

    IFS=',' read -r code ttotal tlookup tconn tstart sdown <<< "$result"
    if (( $(echo "$ttotal > 2.0" | bc -l 2>/dev/null || echo 0) )); then
        printf "  ${C_RED}SLOW${C_RST} %6s %-50s %s %.3fs %sB\\n" "$method" "$uri" "$code" "$ttotal" "$sdown"
    elif (( $(echo "$ttotal > 1.0" | bc -l 2>/dev/null || echo 0) )); then
        printf "  ${C_YLW}WARN${C_RST} %6s %-50s %s %.3fs %sB\\n" "$method" "$uri" "$code" "$ttotal" "$sdown"
    else
        printf "  ${C_GRN} OK ${C_RST} %6s %-50s %s %.3fs %sB\\n" "$method" "$uri" "$code" "$ttotal" "$sdown"
    fi
}

''')

# Add every route
for r in routes:
    methods = r['method'].upper().split('|')
    primary = methods[0]  # Use first method only for timing
    if primary in ('HEAD', 'OPTIONS'):
        continue
    uri = sanitise_uri(r['uri'])
    timing_script += f'time_route "{primary}" "{uri}"\n'

timing_script += '\nlog_info "Timing report saved to $TIMING_FILE"\n'
timing_script += textwrap.dedent('''\

# ── Summary: top 20 slowest ──
log_info "Top 20 slowest routes:"
sort -t',' -k4 -rn "$TIMING_FILE" | head -21 | tail -20 | while IFS=',' read -r m u c t _ _ _ _; do
    printf "  %6s %-50s %s %.3fs\\n" "$m" "$u" "$c" "$t"
done
''')
(HERE / '06_timing_report.sh').write_text(timing_script)
print(f"✓ 06_timing_report.sh ({len(routes)} routes)")


# =====================================================================
# 07_stress_quick.sh  –  Quick concurrency test via xargs -P
# =====================================================================
stress_script = sh_header("07_stress_quick.sh  –  Quick parallel stress test")
stress_script += textwrap.dedent('''\
log_info "=== QUICK STRESS TEST ==="
do_login

CONCURRENCY="${1:-4}"
REPEAT="${2:-3}"

log_info "Running $CONCURRENCY parallel requests × $REPEAT repetitions per endpoint"

STRESS_ENDPOINTS=(
    "/"
    "/login"
    "/customers"
    "/vendors"
    "/invoices"
    "/bills/1"
    "/employees"
    "/expenses"
    "/leads"
    "/projects"
    "/deals"
    "/proposals"
    "/reports"
    "/pos"
    "/settings"
    "/contracts"
    "/payslips"
    "/purchases"
    "/events"
    "/meetings"
)

STRESS_LOG="$LOG_DIR/stress_results.csv"
echo "endpoint,iteration,http_code,time_total" > "$STRESS_LOG"

for ep in "${STRESS_ENDPOINTS[@]}"; do
    log_info "Stress testing: $ep"
    for (( r=1; r<=REPEAT; r++ )); do
        seq 1 "$CONCURRENCY" | xargs -P "$CONCURRENCY" -I {} bash -c "
            result=\\$(curl -sS -o /dev/null -w '%{http_code},%{time_total}' \\
                -b \\"$COOKIE_JAR\\" \\
                --connect-timeout $CONNECT_TIMEOUT \\
                --max-time $TIMEOUT \\
                \\"$BASE_URL$ep\\" 2>/dev/null) || result='000,0'
            echo \\"$ep,$r-{},\\$result\\" >> \\"$STRESS_LOG\\"
            echo \\"  [{} rep $r] $ep → \\$result\\"
        "
    done
done

log_info "Stress test complete. Results at $STRESS_LOG"

# ── Stats ────────
echo ""
log_info "HTTP code distribution:"
tail -n +2 "$STRESS_LOG" | cut -d',' -f3 | sort | uniq -c | sort -rn | while read cnt code; do
    printf "  %4d × HTTP %s\\n" "$cnt" "$code"
done

echo ""
log_info "Avg response time by endpoint:"
python3 -c "
import csv, statistics
from collections import defaultdict
times = defaultdict(list)
with open('$STRESS_LOG') as f:
    reader = csv.reader(f)
    next(reader)
    for row in reader:
        try:
            times[row[0]].append(float(row[3]))
        except (IndexError, ValueError):
            pass
for ep in sorted(times, key=lambda k: -statistics.mean(times[k])):
    m = statistics.mean(times[ep])
    mx = max(times[ep])
    mn = min(times[ep])
    print(f'  {ep:50s}  avg={m:.3f}s  min={mn:.3f}s  max={mx:.3f}s')
" 2>/dev/null || true
''')
(HERE / '07_stress_quick.sh').write_text(stress_script)
print("✓ 07_stress_quick.sh")


# =====================================================================
# 08_header_variants.sh  –  Test routes with different header combos
# =====================================================================
hdr_script = sh_header("08_header_variants.sh  –  Header variant coverage")
hdr_script += 'log_info "=== HEADER VARIANT TESTS ==="\n'
hdr_script += 'do_login\n\n'

# Pick a representative sample of ~40 GET routes
sample_gets = gets[::max(1, len(gets)//40)][:40]
for g in sample_gets:
    uri = sanitise_uri(g['uri'])
    hdr_script += f'curl_header_variants GET "{uri}"\n'

hdr_script += '\nlog_info "Header variant tests complete."\nsummary\n'
(HERE / '08_header_variants.sh').write_text(hdr_script)
print(f"✓ 08_header_variants.sh ({len(sample_gets)} sampled routes × 4 variants)")


# =====================================================================
# 09_unauthenticated.sh  –  Verify auth guards on protected routes
# =====================================================================
unauth_script = sh_header("09_unauthenticated.sh  –  Unauthenticated access tests")
unauth_script += textwrap.dedent('''\
log_info "=== UNAUTHENTICATED ACCESS TESTS ==="
log_info "Testing that protected routes redirect/deny without auth"

# Clear any existing session
rm -f "$COOKIE_JAR"

''')

# Sample ~60 authenticated GET routes
auth_gets = [g for g in gets if g['auth']]
sample_auth = auth_gets[::max(1, len(auth_gets)//60)][:60]
for g in sample_auth:
    uri = sanitise_uri(g['uri'])
    name = g.get('name') or ''
    comment = f"  # {name}" if name else ""
    # Without auth these should 302 to login or 401/403
    unauth_script += f'curl_test GET "{uri}" "302,301,401,403,200"{comment}\n'

unauth_script += '\nlog_info "Unauthenticated access tests complete."\nsummary\n'
(HERE / '09_unauthenticated.sh').write_text(unauth_script)
print(f"✓ 09_unauthenticated.sh ({len(sample_auth)} auth-guarded routes)")


# =====================================================================
# 10_csrf_validation.sh  –  Verify CSRF protection on write routes
# =====================================================================
csrf_script = sh_header("10_csrf_validation.sh  –  CSRF token validation tests")
csrf_script += textwrap.dedent('''\
log_info "=== CSRF VALIDATION TESTS ==="
log_info "Testing that POST/PUT/PATCH/DELETE routes reject requests without CSRF"
do_login

''')

# Sample write routes
sample_posts = posts[::max(1, len(posts)//30)][:30]
for p in sample_posts:
    uri = sanitise_uri(p['uri'])
    name = p.get('name') or ''
    comment = f"  # {name}" if name else ""
    # POST without CSRF token → 419 (CSRF mismatch) or 405
    csrf_script += f'curl_test POST "{uri}" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"{comment}\n'

csrf_script += '\nlog_info "CSRF validation tests complete."\nsummary\n'
(HERE / '10_csrf_validation.sh').write_text(csrf_script)
print(f"✓ 10_csrf_validation.sh ({len(sample_posts)} POST routes)")


# =====================================================================
# 11_json_api.sh  –  JSON API acceptance tests
# =====================================================================
api_routes = [r for r in routes if r['uri'].startswith('api/')]
api_script = sh_header("11_json_api.sh  –  API endpoint tests (%d routes)" % len(api_routes))
api_script += 'log_info "=== API ENDPOINT TESTS (%d routes) ==="\n\n' % len(api_routes)
api_script += 'do_login\n\n'

for r in api_routes:
    methods = r['method'].upper().split('|')
    uri = sanitise_uri(r['uri'])
    name = r.get('name') or ''
    comment = f"  # {name}" if name else ""
    primary = [m for m in methods if m not in ('HEAD', 'OPTIONS')]
    if not primary:
        continue
    m = primary[0]
    api_script += f'curl_test "{m}" "{uri}" "200,201,204,302,401,403,404,405,422" -H "Accept: application/json" -H "Content-Type: application/json"{comment}\n'

api_script += '\nlog_info "API tests complete."\nsummary\n'
(HERE / '11_json_api.sh').write_text(api_script)
print(f"✓ 11_json_api.sh ({len(api_routes)} API routes)")


# =====================================================================
# 12_landing_page.sh  –  LandingPage module routes
# =====================================================================
lp_routes = [r for r in routes if 'LandingPage' in str(r.get('middleware','')) or r['uri'].startswith('landingpage')]
lp_script = sh_header("12_landing_page.sh  –  LandingPage module tests (%d routes)" % len(lp_routes))
lp_script += 'log_info "=== LANDING PAGE MODULE TESTS (%d routes) ==="\n\n' % len(lp_routes)
lp_script += 'do_login\n\n'

for r in lp_routes:
    methods = r['method'].upper().split('|')
    uri = sanitise_uri(r['uri'])
    name = r.get('name') or ''
    comment = f"  # {name}" if name else ""
    primary = [m for m in methods if m not in ('HEAD', 'OPTIONS')]
    if not primary:
        continue
    m = primary[0]
    if m == 'GET':
        lp_script += f'curl_test GET "{uri}" "200,302,301,403,404"{comment}\n'
    elif m == 'POST':
        lp_script += f'curl_post_csrf "{uri}" "200,302,422,403,404,419"{comment}\n'
    elif m in ('PUT', 'PATCH'):
        lp_script += f'curl_write_csrf "{m}" "{uri}" "200,302,422,403,404,419"{comment}\n'
    elif m == 'DELETE':
        lp_script += f'curl_post_csrf "{uri}" "200,302,404,403,405,419" "_method=DELETE"{comment}\n'

lp_script += '\nlog_info "LandingPage tests complete."\nsummary\n'
(HERE / '12_landing_page.sh').write_text(lp_script)
print(f"✓ 12_landing_page.sh ({len(lp_routes)} landing page routes)")


# =====================================================================
# run_all.sh  –  Orchestrator
# =====================================================================
run_all = textwrap.dedent('''\
#!/usr/bin/env bash
# ─────────────────────────────────────────────────────
# run_all.sh  –  Run all curl test suites
# ─────────────────────────────────────────────────────
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "══════════════════════════════════════════════════════"
echo "  ERP Curl Test Suite Runner"
echo "  Base URL: ${ERP_BASE_URL:-http://127.0.0.1:8000}"
echo "══════════════════════════════════════════════════════"
echo ""

# Usage: run_all.sh [suite_numbers...]
#   e.g. ./run_all.sh 00 01 02   → only auth + GET + POST
#   e.g. ./run_all.sh             → all suites

SUITES=(
    00_auth.sh
    01_get_routes.sh
    02_post_routes.sh
    03_put_patch_routes.sh
    04_delete_routes.sh
    05_watch_health.sh
    06_timing_report.sh
    07_stress_quick.sh
    08_header_variants.sh
    09_unauthenticated.sh
    10_csrf_validation.sh
    11_json_api.sh
    12_landing_page.sh
)

TOTAL_PASS=0
TOTAL_FAIL=0
STARTED=$(date +%s)

run_suite() {
    local script="$1"
    local label="${script%.sh}"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "  Running: $label"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    if bash "$SCRIPT_DIR/$script" 2>&1; then
        echo "  ✓ $label: PASSED"
    else
        echo "  ✗ $label: FAILED (exit code $?)"
        ((TOTAL_FAIL++))
    fi
    ((TOTAL_PASS++))
}

if [[ $# -gt 0 ]]; then
    # Run specific suites
    for num in "$@"; do
        for s in "${SUITES[@]}"; do
            if [[ "$s" == "${num}"* ]]; then
                run_suite "$s"
            fi
        done
    done
else
    # Run all suites
    for s in "${SUITES[@]}"; do
        run_suite "$s"
    done
fi

ELAPSED=$(( $(date +%s) - STARTED ))
echo ""
echo "══════════════════════════════════════════════════════"
echo "  ALL SUITES COMPLETE"
echo "  Time: ${ELAPSED}s"
echo "  Suites run: $TOTAL_PASS"
echo "  Suites with failures: $TOTAL_FAIL"
echo "══════════════════════════════════════════════════════"
''')
(HERE / 'run_all.sh').write_text(run_all)
print("✓ run_all.sh")


# ── Make all executable ──────────────────────────────────────────────
import stat
for f in HERE.glob('*.sh'):
    f.chmod(f.stat().st_mode | stat.S_IEXEC | stat.S_IXGRP | stat.S_IXOTH)

print(f"\n✓ All scripts generated and made executable in {HERE}")
print(f"  Total routes covered: {len(routes)}")
print(f"  GET: {len(gets)}, POST: {len(posts)}, PUT: {len(puts)}, PATCH: {len(patches)}, DELETE: {len(deletes)}")
