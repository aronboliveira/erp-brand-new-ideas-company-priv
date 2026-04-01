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
export TEST_EMAIL="${ERP_TEST_EMAIL:-u_1ecb6d5a-e2c5-4961-af3b-0ad83f9d259c@test.local}"
export TEST_PASS="${ERP_TEST_PASS:-Admin@1234}"

mkdir -p "$LOG_DIR"

# ── Colours ──────────────────────────────────────────
if [[ -t 1 ]]; then
    C_RST="\033[0m"; C_RED="\033[0;31m"; C_GRN="\033[0;32m"
    C_YLW="\033[0;33m"; C_BLU="\033[0;34m"; C_CYN="\033[0;36m"
    C_BLD="\033[1m"
else
    C_RST=""; C_RED=""; C_GRN=""; C_YLW=""; C_BLU=""; C_CYN=""; C_BLD=""
fi

# ── Counters ─────────────────────────────────────────
PASS=0; FAIL=0; SKIP=0; WARN=0; TOTAL=0

# ── Curl write-out format ────────────────────────────
# Extensive timing and metadata output
W_FMT='{"http_code":%{http_code},"time_total":%{time_total},"time_namelookup":%{time_namelookup},"time_connect":%{time_connect},"time_appconnect":%{time_appconnect},"time_pretransfer":%{time_pretransfer},"time_starttransfer":%{time_starttransfer},"time_redirect":%{time_redirect},"size_download":%{size_download},"size_upload":%{size_upload},"speed_download":%{speed_download},"url_effective":"%{url_effective}","num_redirects":%{num_redirects},"redirect_url":"%{redirect_url}","content_type":"%{content_type}","ssl_verify_result":%{ssl_verify_result}}'

# Compact timing format for quick scans
W_TIMING='%{http_code} %{time_total}s %{size_download}B %{url_effective}\n'

# ── Helper functions ─────────────────────────────────

log_info()  { printf "${C_BLU}[INFO]${C_RST}  %s\n" "$*"; }
log_pass()  { printf "${C_GRN}[PASS]${C_RST}  %s\n" "$*"; ((PASS++)) || true; ((TOTAL++)) || true; }
log_fail()  { printf "${C_RED}[FAIL]${C_RST}  %s\n" "$*"; ((FAIL++)) || true; ((TOTAL++)) || true; }
log_warn()  { printf "${C_YLW}[WARN]${C_RST}  %s\n" "$*"; ((WARN++)) || true; }
log_skip()  { printf "${C_CYN}[SKIP]${C_RST}  %s\n" "$*"; ((SKIP++)) || true; }

summary() {
    echo ""
    printf "${C_BLD}═══════════════════════════════════════════════════════${C_RST}\n"
    printf "${C_BLD}  RESULTS:${C_RST}  "
    printf "${C_GRN}%d pass${C_RST}  " "$PASS"
    printf "${C_RED}%d fail${C_RST}  " "$FAIL"
    printf "${C_YLW}%d warn${C_RST}  " "$WARN"
    printf "${C_CYN}%d skip${C_RST}  " "$SKIP"
    printf "/ ${C_BLD}%d total${C_RST}\n" "$TOTAL"
    printf "${C_BLD}═══════════════════════════════════════════════════════${C_RST}\n"
    if (( FAIL > 0 )); then
        return 1
    fi
    return 0
}

# ── CSRF extraction ─────────────────────────────────
fetch_csrf() {
    # Remove stale cookies to ensure a clean session
    rm -f "$COOKIE_JAR"
    local html
    html="$(curl -sS -c "$COOKIE_JAR" \
        --connect-timeout "$CONNECT_TIMEOUT" \
        --max-time "$TIMEOUT" \
        "$BASE_URL/login" 2>/dev/null)" || true
    CSRF_TOKEN="$(printf '%s' "$html" | grep -oP 'name="_token"\s+value="\K[^"]+' | head -1)" || true
    if [[ -z "$CSRF_TOKEN" ]]; then
        CSRF_TOKEN="$(printf '%s' "$html" | grep -oP 'csrf-token.*?content="\K[^"]+' | head -1)" || true
    fi
    export CSRF_TOKEN
    [[ -n "$CSRF_TOKEN" ]] && return 0 || return 1
}

# ── Authenticated login ─────────────────────────────
do_login() {
    log_info "Logging in as $TEST_EMAIL ..."
    fetch_csrf || { log_fail "Could not fetch CSRF token"; return 1; }

    local resp
    resp="$(curl -sS -w '\n%{http_code}' \
        -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
        --connect-timeout "$CONNECT_TIMEOUT" \
        --max-time "$TIMEOUT" \
        -X POST "$BASE_URL/login" \
        --data-urlencode "email=$TEST_EMAIL" \
        --data-urlencode "password=$TEST_PASS" \
        --data-urlencode "_token=$CSRF_TOKEN" \
        -H "Content-Type: application/x-www-form-urlencoded" \
        2>/dev/null)"
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
        result="$(curl -sS -w '\n%{http_code}' \
            -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
            --connect-timeout "$CONNECT_TIMEOUT" \
            --max-time "$TIMEOUT" \
            -X "$method" \
            "${extra_flags[@]}" \
            "$url" 2>/dev/null)" || true

        code="$(echo "$result" | tail -1)"
        body="$(echo "$result" | sed '$ d')"

        # Connection failure → retry
        if [[ -z "$code" || "$code" == "000" ]]; then
            ((attempt++)) || true
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
    curl -sS -w "$W_FMT" \
        -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
        --connect-timeout "$CONNECT_TIMEOUT" \
        --max-time "$TIMEOUT" \
        -X "$method" \
        -o /dev/null "$@" \
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
    curl_test "$method" "$uri" "200,302,301,401,403,405,422" \
        -H "Accept: application/json"

    # XMLHttpRequest (AJAX)
    curl_test "$method" "$uri" "200,302,301,401,403,405,422" \
        -H "X-Requested-With: XMLHttpRequest"

    # Both JSON + AJAX
    curl_test "$method" "$uri" "200,302,301,401,403,405,422" \
        -H "Accept: application/json" \
        -H "X-Requested-With: XMLHttpRequest"
}

# ── POST with CSRF ──────────────────────────────────
curl_post_csrf() {
    local uri="${1:?}" expected="${2:-200,302,422}" data="${3:-}"
    fetch_csrf 2>/dev/null || true
    curl_test "POST" "$uri" "$expected" \
        -H "Content-Type: application/x-www-form-urlencoded" \
        -d "_token=$CSRF_TOKEN${data:+&$data}"
}

# ── PUT/PATCH with CSRF ─────────────────────────────
curl_write_csrf() {
    local method="${1:?}" uri="${2:?}" expected="${3:-200,302,422}" data="${4:-}"
    fetch_csrf 2>/dev/null || true
    curl_test "$method" "$uri" "$expected" \
        -H "Content-Type: application/x-www-form-urlencoded" \
        -d "_token=$CSRF_TOKEN&_method=$method${data:+&$data}"
}

# ══════════════════════════════════════════════════════
# CONTENT VALIDATION HELPERS
# Usage:
#   curl_test_content METHOD URI EXPECTED_CODES CONTENT_TYPE [EXTRA_FLAGS...]
#   CONTENT_TYPE: table | card | list | grid | form | any
#   - "table"  → expects <table with at least one <tr>/<td
#   - "card"   → expects card-body / card-header
#   - "list"   → expects <ul/<ol with <li items
#   - "grid"   → expects row/col grid layout
#   - "form"   → expects <form with <input/<select
#   - "any"    → expects any non-trivial HTML (>500 bytes, has structural tags)
#   Combine with +: "table+card" checks both
# ══════════════════════════════════════════════════════

CONTENT_PASS=0; CONTENT_FAIL=0; CONTENT_SKIP=0; CONTENT_TOTAL=0

log_content_pass() { printf "${C_GRN}[CONTENT-PASS]${C_RST}  %s\n" "$*"; ((CONTENT_PASS++)) || true; ((CONTENT_TOTAL++)) || true; }
log_content_fail() { printf "${C_RED}[CONTENT-FAIL]${C_RST}  %s\n" "$*"; ((CONTENT_FAIL++)) || true; ((CONTENT_TOTAL++)) || true; }
log_content_skip() { printf "${C_CYN}[CONTENT-SKIP]${C_RST}  %s\n" "$*"; ((CONTENT_SKIP++)) || true; ((CONTENT_TOTAL++)) || true; }

content_summary() {
    echo ""
    printf "${C_BLD}──── CONTENT VALIDATION ────────────────────────────────${C_RST}\n"
    printf "${C_BLD}  CONTENT:${C_RST}  "
    printf "${C_GRN}%d pass${C_RST}  " "$CONTENT_PASS"
    printf "${C_RED}%d fail${C_RST}  " "$CONTENT_FAIL"
    printf "${C_CYN}%d skip${C_RST}  " "$CONTENT_SKIP"
    printf "/ ${C_BLD}%d total${C_RST}\n" "$CONTENT_TOTAL"
    printf "${C_BLD}────────────────────────────────────────────────────────${C_RST}\n"
    if (( CONTENT_FAIL > 0 )); then return 1; fi
    return 0
}

# Fetch body and HTTP code for a GET request; sets LAST_BODY and LAST_CODE
curl_fetch_body() {
    local uri="${1:?}"
    local url="$BASE_URL$uri"
    local attempt=0

    LAST_BODY=""
    LAST_CODE=""

    while (( attempt <= MAX_RETRIES )); do
        local result
        result="$(curl -sS -w '\n%{http_code}' \
            -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
            --connect-timeout "$CONNECT_TIMEOUT" \
            --max-time "$TIMEOUT" \
            -L \
            "$url" 2>/dev/null)" || true

        LAST_CODE="$(echo "$result" | tail -1)"
        LAST_BODY="$(echo "$result" | sed '$ d')"

        if [[ -z "$LAST_CODE" || "$LAST_CODE" == "000" ]]; then
            ((attempt++)) || true
            sleep 1
            continue
        fi
        break
    done
}

# Check body for table with data rows
# Returns 0 if table has rows, 1 if table exists but empty, 2 if no table
_assert_table() {
    local body="$1"
    # Use >/dev/null instead of -q to avoid SIGPIPE with pipefail on large bodies
    if ! echo "$body" | grep -i '<table' >/dev/null 2>&1; then return 2; fi
    # Count data rows (tr inside tbody, or any tr with td)
    local row_count
    row_count="$(echo "$body" | grep -ci '<td' || true)"
    if (( row_count > 0 )); then return 0; fi
    # Table structure exists but no data cells
    return 1
}

# Check body for card elements with content
_assert_card() {
    local body="$1"
    if echo "$body" | grep -i 'card-body\|card-header\|class="card"' >/dev/null 2>&1; then
        local content_len
        content_len="$(echo "$body" | grep -oiP 'card-body[^<]*>.*?(?=</div)' | wc -c || true)"
        if (( content_len > 20 )); then return 0; fi
        # Cards exist but appear empty
        return 1
    fi
    return 2
}

# Check body for lists with items
_assert_list() {
    local body="$1"
    if ! echo "$body" | grep -i '<ul\|<ol\|list-group' >/dev/null 2>&1; then return 2; fi
    local item_count
    item_count="$(echo "$body" | grep -ci '<li' || true)"
    if (( item_count > 0 )); then return 0; fi
    return 1
}

# Check body for grid layout
_assert_grid() {
    local body="$1"
    if echo "$body" | grep -i 'class="row\|class="col-\|class="grid\|display:.*grid' >/dev/null 2>&1; then
        return 0
    fi
    return 2
}

# Check body for form with inputs
_assert_form() {
    local body="$1"
    if ! echo "$body" | grep -i '<form' >/dev/null 2>&1; then return 2; fi
    local input_count
    input_count="$(echo "$body" | grep -ci '<input\|<select\|<textarea' || true)"
    if (( input_count > 0 )); then return 0; fi
    return 1
}

# Check body has non-trivial HTML content (not just an empty layout)
_assert_any_content() {
    local body="$1"
    local body_len=${#body}
    if (( body_len < 500 )); then return 1; fi
    # Must have at least some structural elements
    # Use >/dev/null instead of -q to avoid SIGPIPE with pipefail on large bodies
    if echo "$body" | grep -i '<div\|<section\|<main\|<article' >/dev/null 2>&1; then
        return 0
    fi
    return 1
}

# ── Main content-validated curl test ─────────────────
# Usage: curl_test_content GET "/route" "200,302" "table+card" [extra flags...]
curl_test_content() {
    local method="${1:?}" uri="${2:?}" expected="${3:-200,302}" content_type="${4:-any}"
    shift 4
    local extra_flags=("$@")
    local url="$BASE_URL$uri"
    local label="$method $uri"

    # Fetch the response
    local attempt=0 code="" body=""
    while (( attempt <= MAX_RETRIES )); do
        local result
        result="$(curl -sS -w '\n%{http_code}' \
            -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
            --connect-timeout "$CONNECT_TIMEOUT" \
            --max-time "$TIMEOUT" \
            -X "$method" \
            -L \
            "${extra_flags[@]}" \
            "$url" 2>/dev/null)" || true

        code="$(echo "$result" | tail -1)"
        body="$(echo "$result" | sed '$ d')"

        if [[ -z "$code" || "$code" == "000" ]]; then
            ((attempt++)) || true
            sleep 1
            continue
        fi
        break
    done

    # 1. Status code check
    local ok=false
    IFS=',' read -ra EXPECTED_ARR <<< "$expected"
    for ec in "${EXPECTED_ARR[@]}"; do
        if [[ "$code" == "$ec" ]]; then ok=true; break; fi
    done

    if ! $ok; then
        log_fail "$label → $code (expected: $expected)"
        log_content_fail "$label → wrong status code: $code"
        return 1
    fi

    # If response is a redirect (3xx), skip content checks
    if [[ "$code" =~ ^3 ]]; then
        log_pass "$label → $code (redirect, content check skipped)"
        log_content_skip "$label → redirect $code"
        return 0
    fi

    # If 403/404/405/429, skip content checks (access denied, not found, or rate limited)
    if [[ "$code" =~ ^(403|404|405|429)$ ]]; then
        log_pass "$label → $code"
        log_content_skip "$label → $code (no content expected)"
        return 0
    fi

    # 2. Content validation
    local content_ok=true
    local content_msg=""
    IFS='+' read -ra types <<< "$content_type"
    for ct in "${types[@]}"; do
        local rc=0
        case "$ct" in
            table)
                _assert_table "$body" && rc=0 || rc=$?
                if (( rc == 1 )); then
                    content_ok=false
                    content_msg+="table exists but EMPTY (no <td> rows); "
                elif (( rc == 2 )); then
                    content_ok=false
                    content_msg+="no <table> found; "
                fi
                ;;
            card)
                _assert_card "$body" && rc=0 || rc=$?
                if (( rc == 1 )); then
                    content_ok=false
                    content_msg+="cards exist but appear EMPTY; "
                elif (( rc == 2 )); then
                    content_ok=false
                    content_msg+="no card elements found; "
                fi
                ;;
            list)
                _assert_list "$body" && rc=0 || rc=$?
                if (( rc == 1 )); then
                    content_ok=false
                    content_msg+="list exists but EMPTY (no <li>); "
                elif (( rc == 2 )); then
                    content_ok=false
                    content_msg+="no <ul>/<ol>/list-group found; "
                fi
                ;;
            grid)
                _assert_grid "$body" && rc=0 || rc=$?
                if (( rc == 2 )); then
                    content_ok=false
                    content_msg+="no grid/row/col layout found; "
                fi
                ;;
            form)
                _assert_form "$body" && rc=0 || rc=$?
                if (( rc == 1 )); then
                    content_ok=false
                    content_msg+="form exists but no inputs; "
                elif (( rc == 2 )); then
                    content_ok=false
                    content_msg+="no <form> found; "
                fi
                ;;
            any)
                _assert_any_content "$body" && rc=0 || rc=$?
                if (( rc != 0 )); then
                    content_ok=false
                    content_msg+="body too small or no structural HTML; "
                fi
                ;;
            *)
                content_msg+="unknown content type '$ct'; "
                ;;
        esac
    done

    if $content_ok; then
        log_pass "$label → $code"
        log_content_pass "$label → content OK ($content_type)"
    else
        log_pass "$label → $code"  # status was fine
        log_content_fail "$label → $content_msg"
        if (( VERBOSE )); then
            echo "  Body length: ${#body} bytes"
            echo "  Body (first 300 chars): ${body:0:300}"
        fi
    fi

    echo "content,$method,$uri,$code,$content_ok,$content_type,$(date +%s)" >> "$LOG_DIR/content_validation.csv" 2>/dev/null || true
}

# ── Flexible content test: tolerant of empty data ────
# Like curl_test_content but treats empty tables/lists as warnings (WARN) not failures.
# Useful for resources that might legitimately have no data in test DB.
curl_test_content_lenient() {
    local method="${1:?}" uri="${2:?}" expected="${3:-200,302}" content_type="${4:-any}"
    shift 4 || true
    local extra_flags=("$@")
    local url="$BASE_URL$uri"
    local label="$method $uri"

    # Fetch
    local attempt=0 code="" body=""
    while (( attempt <= MAX_RETRIES )); do
        local result
        result="$(curl -sS -w '\n%{http_code}' \
            -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
            --connect-timeout "$CONNECT_TIMEOUT" \
            --max-time "$TIMEOUT" \
            -X "$method" \
            -L \
            "${extra_flags[@]}" \
            "$url" 2>/dev/null)" || true

        code="$(echo "$result" | tail -1)"
        body="$(echo "$result" | sed '$ d')"

        if [[ -z "$code" || "$code" == "000" ]]; then
            ((attempt++)) || true
            sleep 1
            continue
        fi
        break
    done

    # Status check
    local ok=false
    IFS=',' read -ra EXPECTED_ARR <<< "$expected"
    for ec in "${EXPECTED_ARR[@]}"; do
        if [[ "$code" == "$ec" ]]; then ok=true; break; fi
    done

    if ! $ok; then
        log_fail "$label → $code (expected: $expected)"
        return 1
    fi

    if [[ "$code" =~ ^(3[0-9]{2}|403|404|405|429)$ ]]; then
        log_pass "$label → $code (skip content)"
        log_content_skip "$label → $code"
        return 0
    fi

    # Content check — lenient mode: empty data = WARN not FAIL
    local content_ok=true
    IFS='+' read -ra types <<< "$content_type"
    for ct in "${types[@]}"; do
        local rc=0
        case "$ct" in
            table) _assert_table "$body" && rc=0 || rc=$?;;
            card)  _assert_card "$body" && rc=0 || rc=$?;;
            list)  _assert_list "$body" && rc=0 || rc=$?;;
            grid)  _assert_grid "$body" && rc=0 || rc=$?;;
            form)  _assert_form "$body" && rc=0 || rc=$?;;
            any)   _assert_any_content "$body" && rc=0 || rc=$?;;
        esac
        if (( rc == 1 )); then
            # Structure exists but no data → warn
            log_warn "$label → $ct structure exists but EMPTY"
            log_pass "$label → $code"
            log_content_skip "$label → $ct empty (lenient)"
            return 0
        elif (( rc == 2 )); then
            content_ok=false
        fi
    done

    if $content_ok; then
        log_pass "$label → $code"
        log_content_pass "$label → content OK ($content_type)"
    else
        log_pass "$label → $code"
        log_content_fail "$label → missing $content_type structure in response"
    fi
}
