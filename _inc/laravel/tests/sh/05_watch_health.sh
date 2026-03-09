#!/usr/bin/env bash
# ─────────────────────────────────────────────────────
# 05_watch_health.sh  –  Periodic health checks via watch
# Auto-generated — do not edit by hand.
# ─────────────────────────────────────────────────────
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=_common.sh
source "$SCRIPT_DIR/_common.sh"

# This script monitors key endpoints every N seconds.
# Usage: ./05_watch_health.sh [interval_secs] [count]
INTERVAL="${1:-5}"
MAX_CHECKS="${2:-20}"

ENDPOINTS=(
    "/"
    "/login"
    "/settings"
    "/customers"
    "/vendors"
    "/invoices"
    "/bills/1"
    "/employees"
    "/expenses"
    "/leads"
    "/projects"
    "/deals"
    "/proposals"
    "/reports"
    "/pos"
)

do_login

log_info "Monitoring ${#ENDPOINTS[@]} endpoints every ${INTERVAL}s for ${MAX_CHECKS} cycles..."
echo "timestamp,endpoint,http_code,time_total,size_download" > "$LOG_DIR/health_watch.csv"

for (( i=1; i<=MAX_CHECKS; i++ )); do
    printf "\n${C_BLD}── Cycle %d/%d ──────────────────────────────${C_RST}\n" "$i" "$MAX_CHECKS"
    for ep in "${ENDPOINTS[@]}"; do
        result="$(curl -sS -o /dev/null -w '%{http_code},%{time_total},%{size_download}' \
            -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
            --connect-timeout "$CONNECT_TIMEOUT" \
            --max-time "$TIMEOUT" \
            "$BASE_URL$ep" 2>/dev/null)" || result="000,0,0"
        echo "$(date +%s),$ep,$result" >> "$LOG_DIR/health_watch.csv"

        IFS=',' read -r code ttime ssize <<< "$result"
        if [[ "$code" =~ ^(200|302|301)$ ]]; then
            printf "  ${C_GRN}%s${C_RST} %s  %.3fs  %sB\n" "$code" "$ep" "$ttime" "$ssize"
        else
            printf "  ${C_RED}%s${C_RST} %s  %.3fs  %sB\n" "$code" "$ep" "$ttime" "$ssize"
        fi
    done
    if (( i < MAX_CHECKS )); then
        sleep "$INTERVAL"
    fi
done

log_info "Health watch complete. CSV at $LOG_DIR/health_watch.csv"
