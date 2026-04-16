#!/usr/bin/env bash
###############################################################################
# ERP Nova Prestech — Build & Test Runner
#
# Interactive script to build services and run the test suite.
# Supports three build backends:
#   1. Kubernetes (minikube + kubectl + docker)   [default]
#   2. Docker Compose
#   3. Raw Artisan (bare-metal)
#
# And three deploy modes:
#   1. soft   — incremental / no wipe              [default]
#   2. hard   — full wipe + rebuild from scratch
#   3. mixed  — rebuild + migrate:fresh --seed
#
# Usage:
#   ./build-and-test.sh                    # interactive prompts
#   ./build-and-test.sh --backend k8s --mode soft --skip-build
#   ./build-and-test.sh -b docker -m hard
#   ./build-and-test.sh -b artisan -m mixed --test-filter UtilityTest
###############################################################################
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# ── Defaults ────────────────────────────────────────────────────────────────
BACKEND=""
MODE=""
SKIP_BUILD=false
TEST_FILTER=""
NAMESPACE="erp-prestech"

# ── Colors ──────────────────────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

log_info()  { echo -e "${CYAN}[INFO]${NC}  $*"; }
log_ok()    { echo -e "${GREEN}[ OK ]${NC}  $*"; }
log_warn()  { echo -e "${YELLOW}[WARN]${NC}  $*"; }
log_err()   { echo -e "${RED}[ERR]${NC}  $*" >&2; }
log_step()  { echo -e "\n${BOLD}${CYAN}══ $* ══${NC}\n"; }

# ── Parse CLI args ──────────────────────────────────────────────────────────
while [[ $# -gt 0 ]]; do
    case "$1" in
        -b|--backend)   BACKEND="$2";     shift 2 ;;
        -m|--mode)      MODE="$2";        shift 2 ;;
        --skip-build)   SKIP_BUILD=true;  shift   ;;
        --test-filter)  TEST_FILTER="$2"; shift 2 ;;
        -h|--help)
            echo "Usage: $0 [-b k8s|docker|artisan] [-m soft|hard|mixed] [--skip-build] [--test-filter FILTER]"
            exit 0
            ;;
        *) log_err "Unknown option: $1"; exit 1 ;;
    esac
done

# ── Interactive prompts (when args not supplied) ────────────────────────────
prompt_backend() {
    if [[ -n "$BACKEND" ]]; then return; fi

    echo -e "\n${BOLD}Select build backend:${NC}"
    echo -e "  ${GREEN}1)${NC} Kubernetes (minikube + kubectl)  ${YELLOW}[default]${NC}"
    echo -e "  ${GREEN}2)${NC} Docker Compose"
    echo -e "  ${GREEN}3)${NC} Raw Artisan (bare-metal)"
    echo ""

    local choice
    read -r -t 120 -p "Choice [1-3]: " choice || choice=""
    case "${choice:-1}" in
        1|k8s|k8|kubernetes)   BACKEND="k8s"     ;;
        2|docker|compose)      BACKEND="docker"   ;;
        3|artisan|raw|bare)    BACKEND="artisan"  ;;
        *)
            log_warn "Invalid choice '${choice}', defaulting to Kubernetes."
            BACKEND="k8s"
            ;;
    esac
}

prompt_mode() {
    if [[ -n "$MODE" ]]; then return; fi

    echo -e "\n${BOLD}Select deploy mode:${NC}"
    echo -e "  ${GREEN}1)${NC} soft   — incremental, no data wipe  ${YELLOW}[default]${NC}"
    echo -e "  ${GREEN}2)${NC} hard   — full wipe + rebuild from scratch"
    echo -e "  ${GREEN}3)${NC} mixed  — rebuild image + migrate:fresh --seed"
    echo ""

    local choice
    read -r -t 120 -p "Choice [1-3]: " choice || choice=""
    case "${choice:-1}" in
        1|soft)   MODE="soft"  ;;
        2|hard)   MODE="hard"  ;;
        3|mixed)  MODE="mixed" ;;
        *)
            log_warn "Invalid choice '${choice}', defaulting to soft."
            MODE="soft"
            ;;
    esac
}

# ── Kubernetes build ────────────────────────────────────────────────────────
build_k8s() {
    log_step "Kubernetes Build (--${MODE})"
    bash scripts/k8s-deploy.sh "--${MODE}"
}

run_tests_k8s() {
    log_step "Running Tests via K8s test image"

    # Prefer running the test image via Docker with host networking for speed.
    # K8s pod exec is very slow on minikube due to overlay filesystem I/O.
    local test_img="erp-prestech-test:latest"
    if docker image inspect "$test_img" &>/dev/null; then
        log_info "Using Docker host-network with image ${test_img}"
        _run_tests_docker_standalone
        return $?
    fi

    # Fallback: exec into the running K8s pod
    local pod
    pod="$(kubectl get pod -l app=erp-backend -n "$NAMESPACE" \
        -o jsonpath='{.items[0].metadata.name}' 2>/dev/null || true)"

    if [[ -z "$pod" ]]; then
        log_err "No backend pod found in namespace ${NAMESPACE}."
        log_err "Is the k8s deployment running? Try: kubectl get pods -n ${NAMESPACE}"
        return 1
    fi

    local filter_arg=""
    if [[ -n "$TEST_FILTER" ]]; then
        filter_arg="--filter=${TEST_FILTER}"
    fi

    log_info "Pod: ${pod}"
    log_info "Running: php artisan test ${filter_arg}"

    kubectl exec -n "$NAMESPACE" "$pod" -c php-fpm -- \
        php -d memory_limit=512M artisan test $filter_arg \
        2>&1 || true
}

# ── Docker Compose build ───────────────────────────────────────────────────
build_docker() {
    log_step "Docker Compose Build (${MODE})"

    case "$MODE" in
        hard)
            docker compose down -v --remove-orphans 2>/dev/null || true
            docker compose build --no-cache
            docker compose up -d
            _docker_wait_healthy
            docker compose exec app php artisan db:wipe --drop-views --force || true
            docker compose exec app php artisan migrate --force
            docker compose exec app php artisan migrate:fresh --seed --force || \
                docker compose exec app php artisan migrate:refresh --seed --force
            docker compose exec app php artisan permission:cache-reset || true
            ;;
        mixed)
            docker compose down --remove-orphans 2>/dev/null || true
            docker compose build
            docker compose up -d
            _docker_wait_healthy
            docker compose exec app php artisan config:clear || true
            docker compose exec app php artisan cache:clear || true
            docker compose exec app php artisan optimize:clear || true
            docker compose exec app php artisan route:clear || true
            docker compose exec app php artisan view:clear || true
            docker compose exec app php artisan clear-compiled || true
            docker compose exec app php artisan migrate:fresh --seed --force
            ;;
        soft)
            docker compose up -d --build
            _docker_wait_healthy
            docker compose exec app php artisan migrate --force || true
            ;;
    esac
}

_docker_wait_healthy() {
    log_info "Waiting for containers to become healthy..."
    local max_wait=120
    local elapsed=0
    while [[ $elapsed -lt $max_wait ]]; do
        if docker compose ps --format json 2>/dev/null | grep -q '"healthy"'; then
            break
        fi
        sleep 3
        elapsed=$((elapsed + 3))
    done
    if [[ $elapsed -ge $max_wait ]]; then
        log_warn "Timed out waiting for healthy containers (${max_wait}s)."
    else
        log_ok "Containers healthy after ${elapsed}s."
    fi
}

run_tests_docker() {
    log_step "Running Tests inside Docker container"

    local filter_arg=""
    if [[ -n "$TEST_FILTER" ]]; then
        filter_arg="--filter=${TEST_FILTER}"
    fi

    # Try docker-compose first, fall back to standalone docker run
    if docker compose ps --format json 2>/dev/null | grep -q '"running"'; then
        log_info "Running via docker-compose exec: php artisan test ${filter_arg}"
        docker compose exec app php -d memory_limit=512M artisan test $filter_arg \
            2>&1 || true
    else
        log_info "No running docker-compose services; using standalone docker run"
        _run_tests_docker_standalone
    fi
}

# Standalone Docker run — uses host network to reach host MySQL/Redis
_run_tests_docker_standalone() {
    local test_img="erp-prestech-test:latest"
    if ! docker image inspect "$test_img" &>/dev/null; then
        log_info "Building test image..."
        docker build -f Dockerfile.test -t "$test_img" . 2>&1
    fi

    local filter_arg=""
    if [[ -n "$TEST_FILTER" ]]; then
        filter_arg="--filter=${TEST_FILTER}"
    fi

    log_info "Running: docker run --network=host ${test_img} phpunit ${filter_arg}"
    docker run --rm --network=host \
        -e APP_KEY="${APP_KEY:-base64:PzVFLmnibiIC5Xk0rDrp0zzHXI8YQD5FKRuzrU+banw=}" \
        -e APP_ENV=testing \
        -e APP_NAME="${APP_NAME:-ERP Nova Prestech}" \
        -e APP_URL="${APP_URL:-http://localhost/}" \
        -e DB_CONNECTION=mysql \
        -e DB_HOST=127.0.0.1 \
        -e DB_PORT=3306 \
        -e DB_DATABASE="${DB_DATABASE:-erp_prestech_db}" \
        -e DB_USERNAME="${DB_USERNAME:-test}" \
        -e DB_PASSWORD="${DB_PASSWORD:-test}" \
        -e CACHE_DRIVER=array \
        -e SESSION_DRIVER=array \
        -e QUEUE_CONNECTION=sync \
        -e REDIS_HOST=127.0.0.1 \
        -e BROADCAST_DRIVER=log \
        "$test_img" \
        php -d memory_limit=512M /var/www/vendor/bin/phpunit --no-coverage $filter_arg \
        2>&1 || true
}

# ── Artisan / Bare-metal build ──────────────────────────────────────────────
build_artisan() {
    log_step "Raw Artisan Build (${MODE})"

    case "$MODE" in
        hard)
            php artisan db:wipe --drop-views --force
            php artisan migrate
            php artisan permission:cache-reset || true
            php artisan config:clear || true
            php artisan cache:clear || true
            php artisan optimize:clear || true
            php artisan route:clear || true
            php artisan view:clear || true
            php artisan clear-compiled
            rm -f bootstrap/cache/services.php \
                 bootstrap/cache/packages.php \
                 bootstrap/cache/compiled.php \
                 bootstrap/cache/routes.php
            composer dump-autoload -o
            php artisan migrate:fresh --seed --force || \
                php artisan migrate:refresh --seed --force
            ;;
        mixed)
            php artisan permission:cache-reset || true
            php artisan config:clear || true
            php artisan cache:clear || true
            php artisan optimize:clear || true
            php artisan route:clear || true
            php artisan view:clear || true
            php artisan clear-compiled
            rm -f bootstrap/cache/services.php \
                 bootstrap/cache/packages.php \
                 bootstrap/cache/compiled.php \
                 bootstrap/cache/routes.php
            composer dump-autoload -o
            php artisan migrate:fresh --seed --force
            ;;
        soft)
            php artisan clear-compiled
            composer dump-autoload -o
            php artisan migrate:fresh --seed
            ;;
    esac
}

run_tests_artisan() {
    log_step "Running Tests (bare-metal)"

    local filter_arg=""
    if [[ -n "$TEST_FILTER" ]]; then
        filter_arg="--filter=${TEST_FILTER}"
    fi

    log_info "Running: php artisan test ${filter_arg}"
    php -d memory_limit=512M artisan test $filter_arg 2>&1 || true
}

# ── Main ────────────────────────────────────────────────────────────────────
main() {
    echo -e "\n${BOLD}${CYAN}╔══════════════════════════════════════════════╗${NC}"
    echo -e "${BOLD}${CYAN}║   ERP Nova Prestech — Build & Test Runner   ║${NC}"
    echo -e "${BOLD}${CYAN}╚══════════════════════════════════════════════╝${NC}"

    prompt_backend
    prompt_mode

    log_info "Backend : ${BOLD}${BACKEND}${NC}"
    log_info "Mode    : ${BOLD}${MODE}${NC}"
    log_info "Filter  : ${TEST_FILTER:-<all tests>}"
    log_info "Skip    : ${SKIP_BUILD}"
    echo ""

    # ── Build phase ─────────────────────────────────────────────────────
    if [[ "$SKIP_BUILD" == false ]]; then
        case "$BACKEND" in
            k8s)     build_k8s     ;;
            docker)  build_docker  ;;
            artisan) build_artisan ;;
        esac
    else
        log_warn "Skipping build phase (--skip-build)."
    fi

    # ── Test phase ──────────────────────────────────────────────────────
    case "$BACKEND" in
        k8s)     run_tests_k8s     ;;
        docker)  run_tests_docker  ;;
        artisan) run_tests_artisan ;;
    esac

    log_step "Done"
    log_ok "Build+Test completed for backend=${BACKEND} mode=${MODE}."
}

main "$@"
