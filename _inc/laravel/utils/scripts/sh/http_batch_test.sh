#!/usr/bin/env bash
# Full HTTP batch test for Laravel routes with wget/curl
BASE="http://127.0.0.1:8000"
OUT="/tmp/http_full_results_20260305.txt"

echo "═══ HTTP Route Tests — $(date) ═══" > "$OUT"
echo "Base URL: $BASE" >> "$OUT"
echo "" >> "$OUT"

declare -A COUNTS
COUNTS[200]=0
COUNTS[301]=0
COUNTS[302]=0
COUNTS[401]=0
COUNTS[403]=0
COUNTS[404]=0
COUNTS[405]=0
COUNTS[500]=0
COUNTS[other]=0

total=0
grep -vE '\{.*\}' /tmp/all_routes.txt | while IFS= read -r route; do
  route="${route//\\/}"
  [[ -z "$route" ]] && continue
  
  uri="${BASE}/${route}"
  code=$(curl -s -o /dev/null -w "%{http_code}" --max-time 3 "$uri" 2>/dev/null)
  
  ((total++))
  echo "[${code}] /${route}" >> "$OUT"
  
  case "$code" in
    200|301|302|401|403|404|405|500) ((COUNTS[$code]++)) ;;
    *) ((COUNTS[other]++)) ;;
  esac
done

echo "" >> "$OUT"
echo "═══ Summary ═══" >> "$OUT"
echo "Total routes tested: $total" >> "$OUT"
for key in "${!COUNTS[@]}"; do
  echo "$key: ${COUNTS[$key]}" >> "$OUT"
done

echo "Test complete. Results in $OUT"
