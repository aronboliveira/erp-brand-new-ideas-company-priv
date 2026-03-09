#!/usr/bin/env bash
# ─────────────────────────────────────────────────────
# 07_stress_quick.sh  –  Quick parallel stress test
# Auto-generated — do not edit by hand.
# ─────────────────────────────────────────────────────
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=_common.sh
source "$SCRIPT_DIR/_common.sh"

log_info "=== QUICK STRESS TEST ==="
do_login

CONCURRENCY="${1:-4}"
REPEAT="${2:-3}"

log_info "Running $CONCURRENCY parallel requests × $REPEAT repetitions per endpoint"

STRESS_ENDPOINTS=(
    "/"
    "/login"
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
    "/settings"
    "/contracts"
    "/payslips"
    "/purchases"
    "/events"
    "/meetings"
)

STRESS_LOG="$LOG_DIR/stress_results.csv"
echo "endpoint,iteration,http_code,time_total" > "$STRESS_LOG"

for ep in "${STRESS_ENDPOINTS[@]}"; do
    log_info "Stress testing: $ep"
    for (( r=1; r<=REPEAT; r++ )); do
        seq 1 "$CONCURRENCY" | xargs -P "$CONCURRENCY" -I {} bash -c "
            result=\$(curl -sS -o /dev/null -w '%{http_code},%{time_total}' \
                -b \"$COOKIE_JAR\" \
                --connect-timeout $CONNECT_TIMEOUT \
                --max-time $TIMEOUT \
                \"$BASE_URL$ep\" 2>/dev/null) || result='000,0'
            echo \"$ep,$r-{},\$result\" >> \"$STRESS_LOG\"
            echo \"  [{} rep $r] $ep → \$result\"
        "
    done
done

log_info "Stress test complete. Results at $STRESS_LOG"

# ── Stats ────────
echo ""
log_info "HTTP code distribution:"
tail -n +2 "$STRESS_LOG" | cut -d',' -f3 | sort | uniq -c | sort -rn | while read cnt code; do
    printf "  %4d × HTTP %s\n" "$cnt" "$code"
done

echo ""
log_info "Avg response time by endpoint:"
python3 -c "
import csv, statistics
from collections import defaultdict
times = defaultdict(list)
with open('$STRESS_LOG') as f:
    reader = csv.reader(f)
    next(reader)
    for row in reader:
        try:
            times[row[0]].append(float(row[3]))
        except (IndexError, ValueError):
            pass
for ep in sorted(times, key=lambda k: -statistics.mean(times[k])):
    m = statistics.mean(times[ep])
    mx = max(times[ep])
    mn = min(times[ep])
    print(f'  {ep:50s}  avg={m:.3f}s  min={mn:.3f}s  max={mx:.3f}s')
" 2>/dev/null || true
