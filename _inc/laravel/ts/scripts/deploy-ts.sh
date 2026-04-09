#!/usr/bin/env bash
# deploy-ts.sh — Build TypeScript and deploy compiled .js to Laravel production paths.
#
# Estratégia de deploy:
# 1. Compila TS via tsc --build
# 2. Copia arquivos compilados de ts/dist/ para os diretórios de produção do Laravel
# 3. Remove marcadores ESM (export {}, import "...vendor-libs") dos .js copiados
# 4. Preserva arquivos originais do core (erp-guard.js, erp-utils.js, erp-bootstrap.min.js)
#    porque os TS são módulos funcionais simplificados, não substitutos 1:1 para os singletons OOP
# 5. Preserva vendor/minificados e resources/js/ (webpack)
#
# Uso: bash ts/scripts/deploy-ts.sh [--dry-run]
#
# PULL REQUEST START — Alteração customizada em arquivo de scripts
set -euo pipefail

LARAVEL_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
TS_ROOT="$LARAVEL_ROOT/ts"
DIST="$TS_ROOT/dist"
DRY_RUN=false

[[ "${1:-}" == "--dry-run" ]] && DRY_RUN=true

log()  { printf '[deploy-ts] %s\n' "$*"; }
die()  { printf '[deploy-ts] ERRO: %s\n' "$*" >&2; exit 1; }
drydo(){ if $DRY_RUN; then log "(dry-run) $*"; else eval "$@"; fi; }

# ────────────────────────────────────────────────────────────────
# 1. Build
# ────────────────────────────────────────────────────────────────
log "=== Etapa 1: Compilação TypeScript ==="
cd "$TS_ROOT"
if ! npx tsc --build 2>&1; then
  die "tsc --build falhou. Corrija os erros antes de continuar."
fi
log "Build concluído com sucesso."

# ────────────────────────────────────────────────────────────────
# 2. Strip ESM markers from dist/ (modify dist/ copies, not originals)
# ────────────────────────────────────────────────────────────────
log "=== Etapa 2: Removendo marcadores ESM do dist/ ==="

strip_esm() {
  local file="$1"
  # Remove standalone "export {};" lines
  sed -i '/^export {};$/d' "$file"
  # Remove side-effect-only vendor-libs imports
  sed -i '/^import ".*declarations\/routes\/vendor-libs";$/d' "$file"
  # Remove empty "export default ..." ONLY if it's just "export default undefined;"
  # (leave real exports alone)
}

STRIP_COUNT=0
while IFS= read -r -d '' jsfile; do
  if grep -qE '^export \{\};$|^import ".*vendor-libs";$' "$jsfile" 2>/dev/null; then
    strip_esm "$jsfile"
    ((STRIP_COUNT++)) || true
  fi
done < <(find "$DIST" -name "*.js" -print0)
log "ESM markers removidos de $STRIP_COUNT arquivos."

# ────────────────────────────────────────────────────────────────
# 3. Deploy: Copy compiled files to production paths
# ────────────────────────────────────────────────────────────────
log "=== Etapa 3: Deploy para caminhos de produção ==="

DEPLOY_COUNT=0
SKIP_COUNT=0

# --- 3a. Route files: dist/public/assets/js/routes/ → public/assets/js/routes/
ROUTE_SRC="$DIST/public/assets/js/routes"
ROUTE_DST="$LARAVEL_ROOT/public/assets/js/routes"
if [[ -d "$ROUTE_SRC" ]]; then
  log "Copiando route files..."
  drydo "rsync -a --include='*/' --include='*.js' --exclude='*' '$ROUTE_SRC/' '$ROUTE_DST/'"
  DEPLOY_COUNT=$((DEPLOY_COUNT + $(find "$ROUTE_SRC" -name "*.js" | wc -l)))
fi

# --- 3b. Page files: dist/public/assets/js/pages/ → public/assets/js/pages/
#     Exceto wow.min.js (vendor)
PAGES_SRC="$DIST/public/assets/js/pages"
PAGES_DST="$LARAVEL_ROOT/public/assets/js/pages"
if [[ -d "$PAGES_SRC" ]]; then
  log "Copiando page files..."
  while IFS= read -r -d '' f; do
    bn=$(basename "$f")
    [[ "$bn" == *".min.js" ]] && { ((SKIP_COUNT++)) || true; continue; }
    drydo "cp '$f' '$PAGES_DST/$bn'"
    ((DEPLOY_COUNT++)) || true
  done < <(find "$PAGES_SRC" -maxdepth 1 -name "*.js" -print0)
fi

# --- 3c. Generic files
GENERIC_SRC="$DIST/public/assets/js/generic"
GENERIC_DST="$LARAVEL_ROOT/public/assets/js/generic"
if [[ -d "$GENERIC_SRC" ]]; then
  log "Copiando generic files..."
  drydo "rsync -a --include='*.js' --exclude='*' '$GENERIC_SRC/' '$GENERIC_DST/'"
  DEPLOY_COUNT=$((DEPLOY_COUNT + $(find "$GENERIC_SRC" -name "*.js" | wc -l)))
fi

# --- 3d. Core delegate files (NOT the big singletons)
#     Deploy: action-delegate, form-submit-delegate, img-fallback-delegate,
#             modal-autoopen, route-guard
#     SKIP:  erp-guard.js, erp-utils.js, erp-bootstrap.js, index.js
CORE_SRC="$DIST/public/assets/js/core"
CORE_DST="$LARAVEL_ROOT/public/assets/js/core"
CORE_SAFE_DEPLOY=(
  "action-delegate.js"
  "form-submit-delegate.js"
  "img-fallback-delegate.js"
  "modal-autoopen.js"
  "route-guard.js"
)
CORE_SKIP=(
  "erp-guard.js"
  "erp-utils.js"
  "erp-bootstrap.js"
  "index.js"
)
if [[ -d "$CORE_SRC" ]]; then
  log "Copiando core delegate files (preservando singletons OOP originais)..."
  for f in "${CORE_SAFE_DEPLOY[@]}"; do
    if [[ -f "$CORE_SRC/$f" ]]; then
      drydo "cp '$CORE_SRC/$f' '$CORE_DST/$f'"
      ((DEPLOY_COUNT++)) || true
    fi
  done
  for f in "${CORE_SKIP[@]}"; do
    log "  SKIP (manter original): core/$f"
    ((SKIP_COUNT++)) || true
  done
fi

# --- 3e. LandingPage files
#     Deploy: dash.js, app.js, pages/*
#     SKIP: vendor-all.js, plugins/
LP_SRC="$DIST/Modules/LandingPage/Resources/assets/js"
LP_DST="$LARAVEL_ROOT/Modules/LandingPage/Resources/assets/js"
if [[ -d "$LP_SRC" ]]; then
  log "Copiando LandingPage files..."
  for f in "$LP_SRC"/*.js; do
    bn=$(basename "$f")
    case "$bn" in
      vendor-all.js|*.min.js) log "  SKIP (vendor): $bn"; ((SKIP_COUNT++)) || true ;;
      *) drydo "cp '$f' '$LP_DST/$bn'"; ((DEPLOY_COUNT++)) || true ;;
    esac
  done
  # Pages subfolder
  if [[ -d "$LP_SRC/pages" ]]; then
    mkdir -p "$LP_DST/pages"
    for f in "$LP_SRC/pages"/*.js; do
      bn=$(basename "$f")
      drydo "cp '$f' '$LP_DST/pages/$bn'"
      ((DEPLOY_COUNT++)) || true
    done
  fi
fi

# --- 3f. Public JS non-vendor files
#     Deploy: custom.js, demo.js, cookie.notice.js, letter.avatar.js, buttons.html5.js
#     SKIP: chatify/autosize.js (has real ESM export default), all vendor .min.js
PJS_SRC="$DIST/public/js"
PJS_DST="$LARAVEL_ROOT/public/js"
PJS_DEPLOY=(
  "custom.js"
  "demo.js"
  "cookie.notice.js"
  "letter.avatar.js"
  "buttons.html5.js"
)
if [[ -d "$PJS_SRC" ]]; then
  log "Copiando public/js files..."
  for f in "${PJS_DEPLOY[@]}"; do
    if [[ -f "$PJS_SRC/$f" ]]; then
      drydo "cp '$PJS_SRC/$f' '$PJS_DST/$f'"
      ((DEPLOY_COUNT++)) || true
    fi
  done
fi

# --- 3g. Chatify code.js (XSS fixes applied)
CHATIFY_SRC="$DIST/public/js/chatify/code.js"
CHATIFY_DST="$LARAVEL_ROOT/public/js/chatify/code.js"
if [[ -f "$CHATIFY_SRC" ]]; then
  log "Copiando chatify/code.js (com correções XSS)..."
  drydo "cp '$CHATIFY_SRC' '$CHATIFY_DST'"
  ((DEPLOY_COUNT++)) || true
fi

# ────────────────────────────────────────────────────────────────
# 4. Summary
# ────────────────────────────────────────────────────────────────
log "=== Deploy concluído ==="
log "Arquivos deployados: $DEPLOY_COUNT"
log "Arquivos ignorados (vendor/singleton): $SKIP_COUNT"
if $DRY_RUN; then
  log "⚠ Modo dry-run. Nenhum arquivo foi alterado."
fi
log "Backup disponível em: .backup/js/20260409/"
# PULL REQUEST END — Alteração customizada em arquivo de scripts
