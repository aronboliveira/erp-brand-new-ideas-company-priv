#!/usr/bin/env bash
# rollback-ts-migration.sh — Revert TypeScript migration changes.
#
# Undoes:
#   1. Compiled dist/ and dist-iife/ output
#   2. Generated TS route files (all 1,097 in ts/src/public/assets/js/routes/)
#   3. Core TS singletons (ts/src/public/assets/js/core/)
#   4. Integration test artifacts
#   5. ESM→IIFE script output
#
# Usage:
#   bash .backup/scripts/bash/20260309_014820/rollback-ts-migration.sh [--dry-run]
#
# The original JS route files in public/assets/js/routes/ are NEVER touched.
#
set -euo pipefail

WORKSPACE="$(cd "$(dirname "$0")/../../../.." && pwd)"
TS_DIR="$WORKSPACE/_inc/laravel/ts"
DRY_RUN=false

[[ "${1:-}" == "--dry-run" ]] && DRY_RUN=true

log() { echo "[rollback] $*"; }
run() {
  if $DRY_RUN; then
    log "DRY-RUN: $*"
  else
    "$@"
  fi
}

log "Workspace: $WORKSPACE"
log "TS dir:    $TS_DIR"
$DRY_RUN && log "** DRY RUN — no changes will be made **"

# 1. Remove compiled output
if [[ -d "$TS_DIR/dist" ]]; then
  log "Removing dist/..."
  run rm -rf "$TS_DIR/dist"
fi

if [[ -d "$TS_DIR/dist-iife" ]]; then
  log "Removing dist-iife/..."
  run rm -rf "$TS_DIR/dist-iife"
fi

# 2. Remove generated TS lang route files (only the 208 lang files from migration)
LANG_DIR="$TS_DIR/src/public/assets/js/routes"
if [[ -d "$LANG_DIR" ]]; then
  LANG_COUNT=$(find "$LANG_DIR" -path '*/lang/*.ts' | wc -l)
  log "Found $LANG_COUNT lang TS files"
  if [[ "$LANG_COUNT" -gt 0 ]]; then
    log "Removing lang TS files..."
    if ! $DRY_RUN; then
      find "$LANG_DIR" -path '*/lang/*.ts' -delete
      # Remove empty lang/ directories
      find "$LANG_DIR" -type d -name 'lang' -empty -delete 2>/dev/null || true
    fi
  fi
fi

# 3. Remove core singletons
CORE_DIR="$TS_DIR/src/public/assets/js/core"
if [[ -d "$CORE_DIR" ]]; then
  log "Removing core TS singletons..."
  run rm -rf "$CORE_DIR"
fi

# 4. Remove integration test artifacts
INTEG_DIR="$TS_DIR/tests/integration"
if [[ -d "$INTEG_DIR" ]]; then
  log "Removing integration tests..."
  run rm -rf "$INTEG_DIR"
fi

# 5. Remove ESM→IIFE script
if [[ -f "$TS_DIR/scripts/esm-to-iife.cjs" ]]; then
  log "Removing ESM→IIFE script..."
  run rm -f "$TS_DIR/scripts/esm-to-iife.cjs"
fi

# 6. Summary
log ""
log "Rollback complete."
log "Original JS files in public/assets/js/routes/ are untouched."
log "To rebuild from scratch: cd $TS_DIR && npx tsc"
