#!/usr/bin/env bash
###############################################################################
# ERP Nova Prestech — Kubernetes Local Deploy (minikube + kubectl)
#
# Uso:
#   ./scripts/k8s-deploy.sh --soft    # Deploy incremental (aplica manifests)
#   ./scripts/k8s-deploy.sh --mixed   # Rebuild imagem + redeploy
#   ./scripts/k8s-deploy.sh --hard    # Limpa tudo, rebuild do zero + seed
#
# Pré-requisitos:
#   - minikube instalado e disponível no PATH
#   - kubectl instalado e disponível no PATH
#   - docker instalado e disponível no PATH
#
# Equivalências com composer serve-*:
#   --soft  → sem wipe de banco, aplica manifests, migrate (correspondente)
#   --mixed → rebuild imagem, redeploy, migrate:fresh --seed
#   --hard  → delete namespace inteiro, rebuild tudo, wipe + migrate --seed
###############################################################################
set -euo pipefail

# ── Configuração ────────────────────────────────────────────────────────────
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LARAVEL_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
K8S_DIR="${LARAVEL_DIR}/k8s"
NAMESPACE="erp-prestech"
APP_IMAGE="erp-prestech-app:latest"
MINIKUBE_PROFILE="${MINIKUBE_PROFILE:-minikube}"
DEPLOY_MODE="${1:---mixed}"

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

log_info()  { echo -e "${CYAN}[INFO]${NC}  $*"; }
log_ok()    { echo -e "${GREEN}[OK]${NC}    $*"; }
log_warn()  { echo -e "${YELLOW}[WARN]${NC}  $*"; }
log_err()   { echo -e "${RED}[ERROR]${NC} $*" >&2; }

# ── Verificações de pré-requisitos ──────────────────────────────────────────
check_prerequisites() {
    local missing=0
    for cmd in minikube kubectl docker; do
        if ! command -v "$cmd" &>/dev/null; then
            log_err "$cmd não encontrado no PATH"
            missing=1
        fi
    done
    if [[ $missing -eq 1 ]]; then
        log_err "Instale as dependências faltantes e tente novamente."
        exit 1
    fi
    log_ok "Pré-requisitos verificados: minikube, kubectl, docker"
}

# ── Garantir minikube rodando ───────────────────────────────────────────────
ensure_minikube() {
    if ! minikube status -p "$MINIKUBE_PROFILE" &>/dev/null; then
        log_info "Iniciando minikube (profile: ${MINIKUBE_PROFILE})..."
        minikube start -p "$MINIKUBE_PROFILE" \
            --cpus=4 \
            --memory=4096 \
            --driver=docker \
            --addons=ingress,metrics-server,dashboard
        log_ok "Minikube iniciado."
    else
        log_ok "Minikube já está rodando."
    fi

    # Habilitar addons se necessário
    minikube addons enable ingress -p "$MINIKUBE_PROFILE" 2>/dev/null || true
    minikube addons enable metrics-server -p "$MINIKUBE_PROFILE" 2>/dev/null || true
}

# ── Configurar Docker para usar o daemon do minikube ────────────────────────
setup_docker_env() {
    log_info "Configurando ambiente Docker do minikube..."
    eval "$(minikube docker-env -p "$MINIKUBE_PROFILE")"
    log_ok "Docker apontando para minikube daemon."
}

# ── Build da imagem Docker ──────────────────────────────────────────────────
build_image() {
    log_info "Construindo imagem Docker: ${APP_IMAGE}..."
    cd "$LARAVEL_DIR"
    docker build -t "$APP_IMAGE" . --no-cache 2>&1 | tail -5
    log_ok "Imagem ${APP_IMAGE} construída com sucesso."
}

build_image_cached() {
    log_info "Construindo imagem Docker (com cache): ${APP_IMAGE}..."
    cd "$LARAVEL_DIR"
    docker build -t "$APP_IMAGE" . 2>&1 | tail -5
    log_ok "Imagem ${APP_IMAGE} construída."
}

# ── Criar namespace ────────────────────────────────────────────────────────
ensure_namespace() {
    if ! kubectl get namespace "$NAMESPACE" &>/dev/null; then
        log_info "Criando namespace ${NAMESPACE}..."
        kubectl apply -f "${K8S_DIR}/namespace.yaml"
        log_ok "Namespace ${NAMESPACE} criado."
    else
        log_ok "Namespace ${NAMESPACE} já existe."
    fi
}

# ── Aplicar manifests K8s ──────────────────────────────────────────────────
apply_manifests() {
    log_info "Aplicando manifests Kubernetes..."
    kubectl apply -f "${K8S_DIR}/backend/config_secrets.yaml"
    kubectl apply -f "${K8S_DIR}/backend/nginx-config.yaml"
    kubectl apply -f "${K8S_DIR}/backend/mysql.yaml"
    kubectl apply -f "${K8S_DIR}/backend/redis.yaml"
    kubectl apply -f "${K8S_DIR}/backend/deployment.yaml"
    kubectl apply -f "${K8S_DIR}/frontend/deployment.yaml"

    # Aguardar o admission webhook do ingress-nginx estar pronto antes de
    # criar o Ingress — evita "context deadline exceeded" no webhook validate.
    log_info "Aguardando ingress-nginx admission webhook estar pronto..."
    kubectl wait --namespace ingress-nginx \
        --for=condition=ready pod \
        --selector=app.kubernetes.io/component=controller \
        --timeout=90s 2>/dev/null \
    || log_warn "ingress-nginx controller não ficou pronto em 90s; tentando aplicar ingress mesmo assim."

    kubectl apply -f "${K8S_DIR}/ingress.yaml"
    log_ok "Manifests aplicados."
}

# ── Aguardar pods ficarem prontos ──────────────────────────────────────────
wait_for_pods() {
    log_info "Aguardando pods ficarem prontos (timeout: 300s)..."
    kubectl wait --for=condition=ready pod \
        -l app=mysql \
        -n "$NAMESPACE" \
        --timeout=120s 2>/dev/null || log_warn "MySQL pod não ficou pronto no tempo esperado."

    kubectl wait --for=condition=ready pod \
        -l app=redis \
        -n "$NAMESPACE" \
        --timeout=60s 2>/dev/null || log_warn "Redis pod não ficou pronto no tempo esperado."

    kubectl wait --for=condition=ready pod \
        -l app=erp-backend \
        -n "$NAMESPACE" \
        --timeout=180s 2>/dev/null || log_warn "Backend pod não ficou pronto no tempo esperado."

    log_ok "Pods prontos."
}

# ── Executar artisan no pod backend ─────────────────────────────────────────
artisan_exec() {
    local pod
    pod="$(kubectl get pod -l app=erp-backend -n "$NAMESPACE" -o jsonpath='{.items[0].metadata.name}' 2>/dev/null)"
    if [[ -z "$pod" ]]; then
        log_err "Nenhum pod backend encontrado."
        return 1
    fi
    log_info "artisan: $*"
    kubectl exec -n "$NAMESPACE" "$pod" -c php-fpm -- php artisan "$@"
}

# ── Rollout restart ─────────────────────────────────────────────────────────
rollout_restart() {
    log_info "Reiniciando deployment erp-backend..."
    kubectl rollout restart deployment/erp-backend -n "$NAMESPACE"
    kubectl rollout status deployment/erp-backend -n "$NAMESPACE" --timeout=180s
    log_ok "Rollout completo."
}

# ── Limpar namespace inteiro ────────────────────────────────────────────────
nuke_namespace() {
    log_warn "Removendo namespace ${NAMESPACE} e todos os recursos..."
    kubectl delete namespace "$NAMESPACE" --ignore-not-found --wait=true --timeout=120s
    log_ok "Namespace ${NAMESPACE} removido."
}

# ── Mostrar status final ───────────────────────────────────────────────────
show_status() {
    echo ""
    log_info "═══════════════════════════════════════════════════════════"
    log_info " Status do Cluster — namespace: ${NAMESPACE}"
    log_info "═══════════════════════════════════════════════════════════"
    kubectl get all -n "$NAMESPACE" 2>/dev/null || true
    echo ""
    kubectl get ingress -n "$NAMESPACE" 2>/dev/null || true
    echo ""

    local mk_ip
    mk_ip="$(minikube ip -p "$MINIKUBE_PROFILE" 2>/dev/null || echo 'N/A')"
    log_info "Minikube IP: ${mk_ip}"
    log_info "Acesso: http://erp.local (adicione '${mk_ip} erp.local' ao /etc/hosts)"
    log_info "Tunnel: minikube tunnel -p ${MINIKUBE_PROFILE}"
    log_info "Dashboard: minikube dashboard -p ${MINIKUBE_PROFILE}"
    log_info "═══════════════════════════════════════════════════════════"
}

# ══════════════════════════════════════════════════════════════════════════════
# MODOS DE DEPLOY
# ══════════════════════════════════════════════════════════════════════════════

deploy_soft() {
    log_info "━━━ DEPLOY SOFT (incremental) ━━━"
    ensure_minikube
    setup_docker_env
    build_image_cached
    ensure_namespace
    apply_manifests
    wait_for_pods
    artisan_exec migrate --force || log_warn "migrate falhou (banco pode não existir ainda)"
    show_status
    log_ok "Deploy SOFT concluído."
}

deploy_mixed() {
    log_info "━━━ DEPLOY MIXED (rebuild + redeploy + fresh seed) ━━━"
    ensure_minikube
    setup_docker_env
    build_image
    ensure_namespace
    apply_manifests
    wait_for_pods
    sleep 5  # Aguardar serviços estabilizarem
    artisan_exec config:clear || true
    artisan_exec cache:clear || true
    artisan_exec optimize:clear || true
    artisan_exec route:clear || true
    artisan_exec view:clear || true
    artisan_exec clear-compiled || true
    artisan_exec migrate:fresh --seed --force || log_warn "migrate:fresh falhou"
    show_status
    log_ok "Deploy MIXED concluído."
}

deploy_hard() {
    log_info "━━━ DEPLOY HARD (nuke + rebuild total) ━━━"
    ensure_minikube
    setup_docker_env

    # Limpa tudo
    nuke_namespace
    sleep 3

    # Rebuild sem cache
    build_image

    # Recria do zero
    ensure_namespace
    apply_manifests
    wait_for_pods
    sleep 10  # Aguardar MySQL inicializar completamente

    # Artisan setup completo
    artisan_exec key:generate --force || true
    artisan_exec config:clear || true
    artisan_exec cache:clear || true
    artisan_exec optimize:clear || true
    artisan_exec route:clear || true
    artisan_exec view:clear || true
    artisan_exec clear-compiled || true
    artisan_exec db:wipe --drop-views --force || true
    artisan_exec migrate --force || log_warn "migrate falhou"
    artisan_exec migrate:fresh --seed --force || log_warn "migrate:fresh --seed falhou"
    artisan_exec permission:cache-reset || true
    show_status
    log_ok "Deploy HARD concluído."
}

# ══════════════════════════════════════════════════════════════════════════════
# MAIN
# ══════════════════════════════════════════════════════════════════════════════

main() {
    echo ""
    log_info "ERP Nova Prestech — Kubernetes Local Deploy"
    log_info "Modo: ${DEPLOY_MODE}"
    echo ""

    check_prerequisites

    case "$DEPLOY_MODE" in
        --soft|-s)
            deploy_soft
            ;;
        --mixed|-m)
            deploy_mixed
            ;;
        --hard|-h)
            deploy_hard
            ;;
        --status)
            ensure_minikube
            show_status
            ;;
        --stop)
            log_info "Parando minikube..."
            minikube stop -p "$MINIKUBE_PROFILE"
            log_ok "Minikube parado."
            ;;
        --destroy)
            nuke_namespace
            log_ok "Recursos K8s removidos. Minikube ainda rodando."
            ;;
        *)
            echo "Uso: $0 {--soft|--mixed|--hard|--status|--stop|--destroy}"
            echo ""
            echo "  --soft    Deploy incremental (build com cache, apply manifests, migrate)"
            echo "  --mixed   Rebuild imagem, redeploy, migrate:fresh --seed"
            echo "  --hard    Nuke namespace, rebuild total do zero, full seed"
            echo "  --status  Mostrar status do cluster"
            echo "  --stop    Parar minikube"
            echo "  --destroy Remover namespace erp-prestech do cluster"
            exit 1
            ;;
    esac
}

main "$@"
