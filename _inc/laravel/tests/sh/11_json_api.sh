#!/usr/bin/env bash
# ─────────────────────────────────────────────────────
# 11_json_api.sh  –  API endpoint tests (2 routes)
# Auto-generated — do not edit by hand.
# ─────────────────────────────────────────────────────
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=_common.sh
source "$SCRIPT_DIR/_common.sh"

log_info "=== API ENDPOINT TESTS (2 routes) ==="

do_login

curl_test "GET" "/api/landingpage" "200,201,204,302,401,403,404,405,422" -H "Accept: application/json" -H "Content-Type: application/json"
curl_test "POST" "/api/logout" "200,201,204,302,401,403,404,405,422" -H "Accept: application/json" -H "Content-Type: application/json"  # auth.logout

log_info "API tests complete."
summary
