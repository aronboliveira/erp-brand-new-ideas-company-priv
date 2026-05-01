#!/usr/bin/env bash
###############################################################################
# ERP Nova Brand New Ideas Company — Kubernetes Local Deploy (minikube + kubectl)
#
# Uso interativo (padrão):
#   ./scripts/k8s-deploy.sh
#
# Flags de resposta automática (combinam com o fluxo interativo):
#   --artifacts-rebuild   Auto-responde "sim" para recompilar artefatos
#   --img-rebuild         Auto-responde "sim" para rebuild da imagem Docker
#   --pod-rebuild         Auto-responde "rollout restart" para pods
#   --pod-delete          Auto-responde "delete + recriar" para pods
#
# Modos legados (ainda suportados para scripts):
#   --soft    Deploy incremental (aplica manifests)
#   --mixed   Rebuild imagem + redeploy
#   --hard    Limpa tudo, rebuild do zero + seed
#
# Fluxo interativo (cada passo tem 60 s de tolerância, padrão em maiúscula):
#   1. Reconstruir artefatos de compilação? [S/n]
#      └─ sim → rebuild Docker + rollout restart (automático, fim)
#      └─ não → passo 2
#   2. Reconstruir imagem Docker? [S/n]
#      └─ sim → rollout restart (automático, fim)
#      └─ não → passo 3
#   3. Ação nos pods?  1=rollout restart  2=delete+recriar  3=nenhuma  [padrão: 1]
#
# Equivalências modos legados:
#   --soft  → sem wipe de banco, aplica manifests, migrate
#   --mixed → rebuild imagem, redeploy, migrate:fresh --seed
#   --hard  → delete namespace inteiro, rebuild tudo, wipe + migrate --seed
###############################################################################
set -euo pipefail

# ── Configuração ────────────────────────────────────────────────────────────
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LARAVEL_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"
K8S_DIR="${LARAVEL_DIR}/k8s"
NAMESPACE="erp-brand-new-ideas-company"
APP_IMAGE="erp-brand-new-ideas-company-app:latest"
MINIKUBE_PROFILE="${MINIKUBE_PROFILE:-minikube}"

# Flags de resposta automática
OPT_ARTIFACTS=false
OPT_IMG=false
OPT_POD_REBUILD=false
OPT_POD_DELETE=false

# Modo legado (vazio = fluxo interativo)
DEPLOY_MODE=""

# ── Parse de argumentos ──────────────────────────────────────────────────────
for arg in "$@"; do
    case "$arg" in
        --artifacts-rebuild) OPT_ARTIFACTS=true  ;;
        --img-rebuild)       OPT_IMG=true        ;;
        --pod-rebuild)       OPT_POD_REBUILD=true ;;
        --pod-delete)        OPT_POD_DELETE=true  ;;
        --soft|-s)           DEPLOY_MODE="soft"   ;;
        --mixed|-m)          DEPLOY_MODE="mixed"  ;;
        --hard|-h)           DEPLOY_MODE="hard"   ;;
        --status)            DEPLOY_MODE="status" ;;
        --stop)              DEPLOY_MODE="stop"   ;;
        --destroy)           DEPLOY_MODE="destroy";;
        --help|help)         DEPLOY_MODE="help"   ;;
    esac
done

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

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

    minikube addons enable ingress -p "$MINIKUBE_PROFILE" 2>/dev/null || true
    minikube addons enable metrics-server -p "$MINIKUBE_PROFILE" 2>/dev/null || true
}

# ── Configurar Docker para usar o daemon do minikube ────────────────────────
setup_docker_env() {
    log_info "Configurando ambiente Docker do minikube..."
    eval "$(minikube docker-env -p "$MINIKUBE_PROFILE")"
    log_ok "Docker apontando para minikube daemon."
}

# ── Reconstruir artefatos de compilação (TS / assets) ───────────────────────
build_artifacts() {
    log_info "Reconstruindo artefatos de compilação (TypeScript / assets)..."
    cd "$LARAVEL_DIR"
    if [[ -f "package.json" ]] && grep -q '"build"' package.json 2>/dev/null; then
        npm run build 2>&1 | tail -10
        log_ok "Artefatos compilados com sucesso."
    else
        log_warn "Script 'build' não encontrado em package.json; pulando compilação de assets."
    fi
    # Composer autoload optimizado para produção
    if command -v composer &>/dev/null; then
        composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -5 || true
        log_ok "Autoload do Composer otimizado."
    fi
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
    log_info "Reiniciando deployment erp-backend (rollout restart)..."
    kubectl rollout restart deployment/erp-backend -n "$NAMESPACE"
    kubectl rollout status deployment/erp-backend -n "$NAMESPACE" --timeout=180s
    log_ok "Rollout completo."
}

# ── Deletar pods e recriar via reapply de manifest ──────────────────────────
delete_recreate_pods() {
    log_info "Deletando deployment erp-backend e reaplicando manifest..."
    kubectl delete -f "${K8S_DIR}/backend/deployment.yaml" \
        --ignore-not-found --wait=true --timeout=60s || true
    kubectl apply -f "${K8S_DIR}/backend/deployment.yaml"
    kubectl rollout status deployment/erp-backend -n "$NAMESPACE" --timeout=180s
    log_ok "Pods recriados com sucesso."
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
# HELPERS DE PROMPT (saída em stdout, mensagens em stderr)
# ══════════════════════════════════════════════════════════════════════════════

# ask_yesno <pergunta> <padrão: s|n>
# Imprime "s" ou "n" no stdout. Timeout de 60 s → padrão.
ask_yesno() {
    local question="$1"
    local default="${2:-s}"
    local label
    [[ "$default" == "s" ]] && label="[S/n]" || label="[s/N]"

    echo -ne "${CYAN}[?]${NC}    ${question} ${label} (60s → '${default}'): " >&2
    local answer
    if read -t 60 -r answer 2>/dev/null; then
        answer="${answer,,}"
        case "$answer" in
            s|sim|y|yes) echo "s" ;;
            n|nao|não|no) echo "n" ;;
            *) echo "$default" ;;
        esac
    else
        echo "" >&2
        log_warn "Tempo esgotado. Usando padrão: '${default}'." >&2
        echo "$default"
    fi
}

# ask_pod_action
# Imprime "1", "2" ou "3" no stdout. Timeout de 60 s → "1".
ask_pod_action() {
    echo -e "${CYAN}[?]${NC}    Ação nos pods:" >&2
    echo -e "         ${YELLOW}1${NC}) Rollout restart   (padrão)" >&2
    echo -e "         ${YELLOW}2${NC}) Delete + recriar  (kubectl delete deployment + reapply)" >&2
    echo -e "         ${YELLOW}3${NC}) Nenhuma ação" >&2
    echo -ne "         Escolha [1/2/3] (60s → '1'): " >&2
    local choice
    if read -t 60 -r choice 2>/dev/null; then
        case "$choice" in
            1|2|3) echo "$choice" ;;
            *) echo "1" ;;
        esac
    else
        echo "" >&2
        log_warn "Tempo esgotado. Usando padrão: rollout restart." >&2
        echo "1"
    fi
}

# ══════════════════════════════════════════════════════════════════════════════
# FLUXO INTERATIVO
# ══════════════════════════════════════════════════════════════════════════════

interactive_deploy() {
    log_info "━━━ DEPLOY INTERATIVO ━━━"
    ensure_minikube
    setup_docker_env
    ensure_namespace
    apply_manifests
    wait_for_pods

    local do_artifacts do_img pod_action

    # ── Passo 1: artefatos de compilação? ─────────────────────────────────
    echo ""
    if [[ "$OPT_ARTIFACTS" == "true" ]]; then
        log_info "Flag --artifacts-rebuild: reconstruindo artefatos de compilação."
        do_artifacts="s"
    else
        do_artifacts="$(ask_yesno 'Reconstruir artefatos de compilação (TS/assets)?' 's')"
    fi

    if [[ "$do_artifacts" == "s" ]]; then
        build_artifacts
        build_image
        rollout_restart
        show_status
        log_ok "Deploy interativo concluído."
        return
    fi

    # ── Passo 2: rebuild da imagem Docker? ────────────────────────────────
    echo ""
    if [[ "$OPT_IMG" == "true" ]]; then
        log_info "Flag --img-rebuild: reconstruindo imagem Docker."
        do_img="s"
    else
        do_img="$(ask_yesno 'Reconstruir imagem Docker?' 's')"
    fi

    if [[ "$do_img" == "s" ]]; then
        build_image
        rollout_restart
        show_status
        log_ok "Deploy interativo concluído."
        return
    fi

    # ── Passo 3: ação nos pods ────────────────────────────────────────────
    echo ""
    if [[ "$OPT_POD_REBUILD" == "true" ]]; then
        log_info "Flag --pod-rebuild: rollout restart."
        pod_action="1"
    elif [[ "$OPT_POD_DELETE" == "true" ]]; then
        log_info "Flag --pod-delete: delete e recriar pods."
        pod_action="2"
    else
        pod_action="$(ask_pod_action)"
    fi

    case "$pod_action" in
        1) rollout_restart ;;
        2) delete_recreate_pods ;;
        3) log_info "Nenhuma ação realizada nos pods." ;;
    esac

    show_status
    log_ok "Deploy interativo concluído."
}

# ══════════════════════════════════════════════════════════════════════════════
# MODOS LEGADOS
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
    sleep 5
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

    nuke_namespace
    sleep 3

    build_image

    ensure_namespace
    apply_manifests
    wait_for_pods
    sleep 10

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

show_help() {
    echo "Uso: $0 [modo-legado | flags-interativas]"
    echo ""
    echo "  Fluxo interativo (padrão quando nenhum modo legado é passado):"
    echo "    Sem argumentos        Prompts guiados com 60 s de tolerância cada"
    echo "    --artifacts-rebuild   Auto: recompilar TS/assets → rebuild Docker → rollout restart"
    echo "    --img-rebuild         Auto: rebuild Docker → rollout restart"
    echo "    --pod-rebuild         Auto: rollout restart"
    echo "    --pod-delete          Auto: delete deployment + reapply"
    echo ""
    echo "  Modos legados:"
    echo "    --soft    Deploy incremental (build com cache, apply manifests, migrate)"
    echo "    --mixed   Rebuild imagem, redeploy, migrate:fresh --seed"
    echo "    --hard    Nuke namespace, rebuild total do zero, full seed"
    echo "    --status  Mostrar status do cluster"
    echo "    --stop    Parar minikube"
    echo "    --destroy Remover namespace erp-brand-new-ideas-company do cluster"
}

main() {
    echo ""
    log_info "ERP Nova Brand New Ideas Company — Kubernetes Local Deploy"
    echo ""

    check_prerequisites

    case "$DEPLOY_MODE" in
        soft)
            deploy_soft
            ;;
        mixed)
            deploy_mixed
            ;;
        hard)
            deploy_hard
            ;;
        status)
            ensure_minikube
            show_status
            ;;
        stop)
            log_info "Parando minikube..."
            minikube stop -p "$MINIKUBE_PROFILE"
            log_ok "Minikube parado."
            ;;
        destroy)
            nuke_namespace
            log_ok "Recursos K8s removidos. Minikube ainda rodando."
            ;;
        help)
            show_help
            ;;
        "")
            # Fluxo interativo (padrão)
            interactive_deploy
            ;;
        *)
            log_err "Modo desconhecido: ${DEPLOY_MODE}"
            show_help
            exit 1
            ;;
    esac
}

main "$@"
