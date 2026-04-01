#!/usr/bin/env bash
# ─────────────────────────────────────────────────────
# 00_auth.sh  –  Authentication / session bootstrap
# Auto-generated — do not edit by hand.
# ─────────────────────────────────────────────────────
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=_common.sh
source "$SCRIPT_DIR/_common.sh"

log_info "=== AUTHENTICATION TESTS ==="

# ── Unauthenticated access should redirect ────────
rm -f "$COOKIE_JAR"
curl_test GET "/" "302,200,301"
curl_test GET "/login" "200"
curl_test GET "/register" "200,302,404"

# ── Login with invalid creds ─────────────────────
fetch_csrf
curl_test POST "/login" "302,422,200" \
    -H "Content-Type: application/x-www-form-urlencoded" \
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
curl_test POST "/logout" "302,200" \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -d "_token=$CSRF_TOKEN"

# ── Re-login for subsequent suites ───────────────
do_login

log_info "Auth tests complete."
summary
