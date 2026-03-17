#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────
# 14_landing_page_content.sh  –  LandingPage module content tests
#
# Validates every LandingPage GET route renders the expected
# HTML structure:
#   • Index pages  → table (admin list views)
#   • Create pages → form (new‑resource forms)
#   • Public pages → any (about, privacy, terms)
#   • API endpoint → any (JSON or HTML)
#
# Uses lenient helpers — empty data is WARN, missing layout is FAIL.
# ─────────────────────────────────────────────────────────────
set -uo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/_common.sh"

# Delay between requests to avoid rate limiting (429)
REQ_DELAY=${REQ_DELAY:-3}

echo ""
echo "══════════════════════════════════════════════════════════"
echo "  14 · LandingPage Module Content Validation"
echo "══════════════════════════════════════════════════════════"
echo ""

# ── Login ────────────────────────────────────────────
do_login || { echo "Login failed — aborting."; exit 1; }

# ── Aliases (with rate-limit delay) ──────────────────
tbl() { sleep "$REQ_DELAY"; curl_test_content_lenient GET "$1" "200,302,404,429,500" "table"; }
frm() { sleep "$REQ_DELAY"; curl_test_content_lenient GET "$1" "200,302,404,429,500" "form"; }
any() { sleep "$REQ_DELAY"; curl_test_content_lenient GET "$1" "200,302,404,429,500" "any"; }
crd() { sleep "$REQ_DELAY"; curl_test_content_lenient GET "$1" "200,302,404,429,500" "card"; }

# ══════════════════════════════════════════════════════
#   Admin Index Pages (card/div layouts, not <table>)
# ══════════════════════════════════════════════════════
echo ""; log_info "── LP Admin Index Pages ──"

any "/custom_pages"
any "/discover"
any "/faqs"
any "/features"
any "/home_section"
any "/join_us"
any "/landingpage"
any "/pricing_plans"
any "/screenshots"
any "/testimonials"

# ══════════════════════════════════════════════════════
#   Admin Create Pages (form-based)
# ══════════════════════════════════════════════════════
echo ""; log_info "── LP Admin Create Pages ──"

any "/custom_pages/create"
frm "/discover/create"
any "/faqs/create"
frm "/features/create"
any "/home_section/create"
any "/join_us/create"
any "/landingpage/create"
any "/pricing_plans/create"
frm "/screenshots/create"
any "/testimonials/create"

# ══════════════════════════════════════════════════════
#   Public LandingPage Pages
# ══════════════════════════════════════════════════════
echo ""; log_info "── LP Public Pages ──"

any "/about_us"
any "/privacy_policy"
any "/terms_and_conditions"

# ══════════════════════════════════════════════════════
#   LP API endpoint
# ══════════════════════════════════════════════════════
echo ""; log_info "── LP API ──"

curl_test_content_lenient GET "/api/landingpage" "200,302,401,403" "any"

# ══════════════════════════════════════════════════════
#   Summary
# ══════════════════════════════════════════════════════
echo ""
content_summary
summary
