#!/usr/bin/env bash
# ─────────────────────────────────────────────────────
# 12_landing_page.sh  –  LandingPage module tests (7 routes)
# Auto-generated — do not edit by hand.
# ─────────────────────────────────────────────────────
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=_common.sh
source "$SCRIPT_DIR/_common.sh"

log_info "=== LANDING PAGE MODULE TESTS (7 routes) ==="

do_login

curl_test GET "/landingpage" "200,302,301,403,404"  # landingpage.index
curl_post_csrf "/landingpage" "200,302,422,403,404,419"  # landingpage.store
curl_test GET "/landingpage/create" "200,302,301,403,404"  # landingpage.create
curl_test GET "/landingpage/1" "200,302,301,403,404"  # landingpage.show
curl_write_csrf "PUT" "/landingpage/1" "200,302,422,403,404,419"  # landingpage.update
curl_post_csrf "/landingpage/1" "200,302,404,403,405,419" "_method=DELETE"  # landingpage.destroy
curl_test GET "/landingpage/1/edit" "200,302,301,403,404"  # landingpage.edit

log_info "LandingPage tests complete."
summary
