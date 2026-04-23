#!/usr/bin/env bash
# ============================================================================
# LandingPage Module — Subagent Operations Script
# ============================================================================
# Usage:  bash _inc/laravel/utils/.llms/notes/context/landingpage/operations.sh <command> [args]
#
# Commands:
#   validate       Run full validation (syntax + tests + curl)
#   syntax         Run php -l on all module PHP files
#   test           Run AuthAndLandingPageTest PHPUnit suite
#   curl           Hit all LP public/auth endpoints and report status codes
#   routes         List all LP-related routes
#   clear          Clear cache, routes, views, config
#   db:check       Dump current state of landing_page_settings table
#   db:seed-pages  Insert default menubar_page settings into DB
#   structure      Print the module file tree
# ============================================================================

set -euo pipefail

# Resolve _inc/laravel from wherever this script lives
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
LARAVEL_ROOT="$(cd "$SCRIPT_DIR/../../../laravel" 2>/dev/null && pwd)" \
  || LARAVEL_ROOT="$(cd "$SCRIPT_DIR/../../../../_inc/laravel" 2>/dev/null && pwd)" \
  || { echo "Cannot resolve _inc/laravel directory"; exit 1; }
cd "$LARAVEL_ROOT"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

ok()   { echo -e "${GREEN}✓${NC} $*"; }
fail() { echo -e "${RED}✗${NC} $*"; }
info() { echo -e "${CYAN}ℹ${NC} $*"; }
warn() { echo -e "${YELLOW}⚠${NC} $*"; }

# ── syntax ──────────────────────────────────────────────────────────────────
cmd_syntax() {
  info "Running php -l on all LandingPage PHP files…"
  local errors=0
  while IFS= read -r f; do
    if ! php -l "$f" > /dev/null 2>&1; then
      fail "$f"
      php -l "$f"
      ((errors++))
    fi
  done < <(find Modules/LandingPage -name '*.php' -type f)
  if [[ $errors -eq 0 ]]; then
    ok "All LandingPage PHP files pass syntax check"
  else
    fail "$errors file(s) with syntax errors"
    return 1
  fi
}

# ── test ────────────────────────────────────────────────────────────────────
cmd_test() {
  info "Running AuthAndLandingPageTest…"
  php artisan test --filter=AuthAndLandingPageTest
}

# ── curl ────────────────────────────────────────────────────────────────────
cmd_curl() {
  local base="${LP_BASE_URL:-http://127.0.0.1:8000}"
  info "Hitting LP endpoints at $base …"

  local -a public_routes=(
    "/about_us"
    "/privacy_policy"
    "/terms_and_conditions"
    "/pages/about_us"
    "/pages/privacy_policy"
    "/pages/terms_and_conditions"
  )

  local -a auth_routes=(
    "/landingpage"
    "/testimonials"
    "/faqs"
    "/features"
    "/discover"
    "/screenshots"
    "/custom_pages"
    "/pricing_plans"
    "/join_us"
    "/home_section"
  )

  echo ""
  echo "── Public routes (expect 200) ──"
  for r in "${public_routes[@]}"; do
    local code
    code=$(curl -sS -o /dev/null -w "%{http_code}" --max-time 10 "${base}${r}" 2>/dev/null || echo "ERR")
    if [[ "$code" == "200" ]]; then
      ok "$r → $code"
    else
      fail "$r → $code (expected 200)"
    fi
  done

  echo ""
  echo "── Auth routes (expect 302 → login) ──"
  for r in "${auth_routes[@]}"; do
    local code
    code=$(curl -sS -o /dev/null -w "%{http_code}" --max-time 10 "${base}${r}" 2>/dev/null || echo "ERR")
    if [[ "$code" == "302" ]]; then
      ok "$r → $code (redirect to login)"
    elif [[ "$code" == "200" ]]; then
      warn "$r → $code (expected 302 — may be logged in)"
    else
      fail "$r → $code (unexpected)"
    fi
  done
}

# ── routes ──────────────────────────────────────────────────────────────────
cmd_routes() {
  info "Listing LandingPage routes…"
  php artisan route:list --path=/ | grep -iE '(landingpage|custom_page|about_us|privacy|terms|faqs|testimonials|features|discover|screenshots|pricing|join_us|home_section)' || warn "No matches found"
}

# ── clear ───────────────────────────────────────────────────────────────────
cmd_clear() {
  info "Clearing caches…"
  php artisan cache:clear 2>/dev/null || true
  php artisan route:clear 2>/dev/null || true
  php artisan view:clear 2>/dev/null || true
  php artisan config:clear 2>/dev/null || true
  ok "All caches cleared"
}

# ── db:check ────────────────────────────────────────────────────────────────
cmd_db_check() {
  info "Checking landing_page_settings table…"
  php artisan tinker --execute="
    use Modules\LandingPage\Entities\LandingPageSetting;
    \$all = LandingPageSetting::all(['name','value'])->toArray();
    echo json_encode(\$all, JSON_PRETTY_PRINT);
  " 2>/dev/null || warn "Could not query LandingPageSetting table"
}

# ── db:seed-pages ───────────────────────────────────────────────────────────
cmd_db_seed_pages() {
  info "Seeding default menubar_page settings…"
  php artisan tinker --execute="
    use Modules\LandingPage\Entities\LandingPageSetting;
    \$pages = json_encode([
      [
        'menubarPageName' => 'About Us',
        'menubarPageContent' => '',
        'pageSlug' => 'about_us',
        'templateName' => 'page_content',
        'pageUrl' => '',
        'header' => 'on',
        'footer' => 'on',
        'login' => 'on'
      ],
      [
        'menubarPageName' => 'Privacy Policy',
        'menubarPageContent' => '',
        'pageSlug' => 'privacy_policy',
        'templateName' => 'page_content',
        'pageUrl' => '',
        'header' => 'on',
        'footer' => 'on',
        'login' => 'on'
      ],
      [
        'menubarPageName' => 'Terms & Conditions',
        'menubarPageContent' => '',
        'pageSlug' => 'terms_and_conditions',
        'templateName' => 'page_content',
        'pageUrl' => '',
        'header' => 'on',
        'footer' => 'on',
        'login' => 'on'
      ]
    ]);
    LandingPageSetting::updateOrCreate(
      ['name' => 'menubar_page'],
      ['value' => \$pages]
    );
    LandingPageSetting::updateOrCreate(
      ['name' => 'menubar_status'],
      ['value' => 'on']
    );
    echo 'Done. menubar_page + menubar_status seeded.';
  " 2>/dev/null || fail "Seeding failed"
}

# ── structure ───────────────────────────────────────────────────────────────
cmd_structure() {
  info "LandingPage module structure:"
  if command -v tree &>/dev/null; then
    tree Modules/LandingPage -I '__pycache__|node_modules' --dirsfirst
  else
    find Modules/LandingPage -type f | sort
  fi
}

# ── validate (all-in-one) ──────────────────────────────────────────────────
cmd_validate() {
  local failed=0
  echo "═══════════════════════════════════════════════"
  echo " LandingPage Module — Full Validation"
  echo "═══════════════════════════════════════════════"
  echo ""

  cmd_clear
  echo ""

  cmd_syntax || ((failed++))
  echo ""

  cmd_test || ((failed++))
  echo ""

  cmd_curl || ((failed++))
  echo ""

  if [[ $failed -eq 0 ]]; then
    echo ""
    ok "All validations passed!"
  else
    echo ""
    fail "$failed validation step(s) failed"
    return 1
  fi
}

# ── main dispatcher ─────────────────────────────────────────────────────────
case "${1:-validate}" in
  validate)      cmd_validate ;;
  syntax)        cmd_syntax ;;
  test)          cmd_test ;;
  curl)          cmd_curl ;;
  routes)        cmd_routes ;;
  clear)         cmd_clear ;;
  db:check)      cmd_db_check ;;
  db:seed-pages) cmd_db_seed_pages ;;
  structure)     cmd_structure ;;
  *)
    echo "Usage: $0 {validate|syntax|test|curl|routes|clear|db:check|db:seed-pages|structure}"
    exit 1
    ;;
esac
