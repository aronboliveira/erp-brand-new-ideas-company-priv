#!/usr/bin/env python3
"""
View Content Smoke Test
For every 200-returning route, fetches the HTML and checks for:
  - Tables with data rows (not just headers)
  - Cards / metric boxes with numbers
  - Finance numbers (currency, %, sums)
  - Lists (ul/ol with li rows)
  - Chart canvases / chart data
  - Empty-state indicators (placeholder messages)
  - Error alerts / warning banners

Outputs a graded report: RICH / PARTIAL / EMPTY / ERROR for each view.
Includes hardware-based thermal throttling.
"""
import subprocess, os, re, json, time, sys
from datetime import datetime
from html.parser import HTMLParser
from pathlib import Path

BASE_URL    = os.environ.get("SMOKE_TEST_URL", "http://127.0.0.1:8888")
COOKIE_FILE = "/tmp/content_test_cookies.txt"
ADMIN_EMAIL = os.environ.get("SMOKE_TEST_EMAIL",    "alexys87@example.org")
ADMIN_PASS  = os.environ.get("SMOKE_TEST_PASSWORD", "Admin@1234")
LARAVEL_DIR = str(Path(__file__).resolve().parents[3])
RESULTS_DIR = os.path.dirname(os.path.abspath(__file__)) + "/../"

CPU_LIMIT   = 600  # sum-across-all-procs threshold (ps -A); VS Code+PHP-LS baseline ~250%
TEMP_LIMIT  = 90   # °C
SLEEP_COOL  = 20   # seconds
REQ_DELAY   = 0.6  # seconds between requests (thermal buffer)

# ---------------------------------------------------------------------------
# Hardware monitor
# ---------------------------------------------------------------------------

def hw_metrics() -> dict:
    m = {"cpu_sum": 0.0, "mem_pct": 0.0, "temp_max": 0.0}
    try:
        ps = subprocess.run(["ps", "-A", "-o", "%cpu"],
                            capture_output=True, text=True, timeout=4)
        vals = [float(x) for x in ps.stdout.split() if re.match(r'^\d+\.?\d*$', x)]
        m["cpu_sum"] = sum(vals)
    except Exception:
        pass
    try:
        fm = subprocess.run(["free", "-m"], capture_output=True, text=True, timeout=3)
        for line in fm.stdout.splitlines():
            if line.startswith("Mem:"):
                p = line.split()
                if len(p) >= 3:
                    m["mem_pct"] = float(p[2]) / float(p[1]) * 100
    except Exception:
        pass
    try:
        sens = subprocess.run(["sensors"], capture_output=True, text=True, timeout=5)
        # Only pick the actual reading (after ':'), not high/crit thresholds in parens
        temps = re.findall(r':\s+\+(\d+\.\d+)°C', sens.stdout)
        if temps:
            m["temp_max"] = max(float(t) for t in temps)
    except Exception:
        pass
    return m


def wait_for_cool(label=""):
    while True:
        m = hw_metrics()
        print(f"  HW: cpu={m['cpu_sum']:.0f}% mem={m['mem_pct']:.0f}% "
              f"temp={m['temp_max']:.0f}°C", end="")
        if m["cpu_sum"] < CPU_LIMIT and m["temp_max"] < TEMP_LIMIT:
            print(" ✓")
            return m
        print(f" ⏸ HOT — sleeping {SLEEP_COOL}s [{label}]")
        time.sleep(SLEEP_COOL)


# ---------------------------------------------------------------------------
# HTML content analyzer
# ---------------------------------------------------------------------------

class ContentAnalyzer(HTMLParser):
    def __init__(self):
        super().__init__()
        self.in_table = False
        self.tr_count = 0
        self.td_count = 0
        self.tbody_rows = 0
        self.in_tbody = False
        self.canvas_count = 0
        self.chart_scripts = 0
        self.li_count = 0
        self.alerts = []
        self.card_numbers = []
        # context
        self._current_tag = ""
        self._text_buf = ""
        self.all_text_fragments = []

    def handle_starttag(self, tag, attrs):
        self._current_tag = tag
        attr_d = dict(attrs)
        if tag == "table":
            self.in_table = True
        elif tag == "tbody":
            self.in_tbody = True
        elif tag == "tr":
            self.tr_count += 1
            if self.in_tbody:
                self.tbody_rows += 1
        elif tag == "td":
            self.td_count += 1
        elif tag == "canvas":
            self.canvas_count += 1
        elif tag == "li":
            self.li_count += 1
        elif tag in ("div", "span", "h4", "h5", "h6", "p"):
            cls = attr_d.get("class", "")
            if any(k in cls for k in ["card", "metric", "badge", "chip",
                                       "stat", "number", "amount", "total"]):
                pass  # will collect text below

    def handle_endtag(self, tag):
        if tag == "table":
            self.in_table = False
        elif tag == "tbody":
            self.in_tbody = False

    def handle_data(self, data):
        stripped = data.strip()
        if not stripped:
            return
        self.all_text_fragments.append(stripped)
        # detect finance numbers: contains digit + optional currency symbol
        if re.search(r'[\$€£R\$¥₹]\s*\d|[\d,\.]+\s*%|\d{1,3}(?:[,\.]\d{3})+', stripped):
            self.card_numbers.append(stripped[:80])
        # detect alerts / empty states
        low = stripped.lower()
        if any(k in low for k in ["no data", "no record", "no result",
                                   "empty", "nothing found", "not found",
                                   "no item", "nenhum", "nenhuma", "vazio"]):
            self.alerts.append(("empty_state", stripped[:100]))
        if any(k in low for k in ["error", "exception", "failed",
                                   "unauthorized", "forbidden"]):
            self.alerts.append(("error_hint", stripped[:100]))


def analyze_html(html: str) -> dict:
    p = ContentAnalyzer()
    try:
        p.feed(html)
    except Exception:
        pass

    all_text = " ".join(p.all_text_fragments)
    # Chart detection: look for Chart.new / chart data in scripts
    chart_refs = len(re.findall(r'new Chart\s*\(|chartData|echart|apexchart|highchart',
                                 html, re.IGNORECASE))

    # Finance numbers found anywhere in the page body
    finance_numbers = re.findall(
        r'(?:R\$|US\$|\$|€|£|¥|₹)\s*[\d,\.]+|[\d,\.]+\s*(?:USD|BRL|EUR|GBP)'
        r'|\d{1,3}(?:[,\.]\d{3})+[\d,\.]*\s*%'
        r'|\d+[\.,]\d{2}',
        html
    )

    data_table_rows = max(0, p.tbody_rows - 1)   # subtract possible header row
    data_li_items   = max(0, p.li_count - 20)     # nav/sidebar li subtracted

    grade = "EMPTY"
    reasons = []
    score = 0

    if p.canvas_count > 0 or chart_refs > 0:
        score += 3
        reasons.append(f"charts:{p.canvas_count}canvas+{chart_refs}chartjs")
    if data_table_rows >= 1:
        score += 4
        reasons.append(f"table_data_rows:{data_table_rows}")
    elif p.tr_count >= 2:
        score += 1
        reasons.append(f"table_rows:{p.tr_count}(maybe_headers_only)")
    if len(finance_numbers) >= 3:
        score += 3
        reasons.append(f"finance_nums:{len(finance_numbers)}")
    elif len(finance_numbers) >= 1:
        score += 1
        reasons.append(f"finance_num:{len(finance_numbers)}")
    if data_li_items >= 5:
        score += 2
        reasons.append(f"list_items:{p.li_count}")
    if len(p.card_numbers) >= 1:
        score += 2
        reasons.append(f"card_numbers:{len(p.card_numbers)}")

    empty_states = [a for a in p.alerts if a[0] == "empty_state"]
    error_hints  = [a for a in p.alerts if a[0] == "error_hint"]
    if empty_states:
        score -= 2
        reasons.append(f"empty_state_msgs:{len(empty_states)}")
    if error_hints:
        score -= 1
        reasons.append(f"error_hints:{len(error_hints)}")

    if score >= 6:
        grade = "RICH"
    elif score >= 3:
        grade = "PARTIAL"
    elif score >= 1:
        grade = "THIN"
    else:
        grade = "EMPTY"

    sample_numbers = list(dict.fromkeys(finance_numbers))[:8]

    return {
        "grade": grade,
        "score": score,
        "reasons": reasons,
        "table_rows":    p.tbody_rows,
        "canvas_count":  p.canvas_count,
        "chart_refs":    chart_refs,
        "li_count":      p.li_count,
        "finance_numbers": sample_numbers,
        "card_numbers":  p.card_numbers[:5],
        "empty_states":  [x[1] for x in empty_states][:3],
        "error_hints":   [x[1] for x in error_hints][:3],
        "html_len":      len(html),
    }


# ---------------------------------------------------------------------------
# HTTP helpers
# ---------------------------------------------------------------------------

def _curl(*args, timeout=20):
    try:
        r = subprocess.run(
            ["curl", "-s", "--max-time", str(timeout)] + list(args),
            capture_output=True, timeout=timeout + 5
        )
        # Decode with replacement for non-UTF-8 responses (binary exports, PDFs, etc.)
        body = r.stdout.decode("utf-8", errors="replace")
        return body, r.returncode
    except subprocess.TimeoutExpired:
        return "", -1


def login():
    os.makedirs("/tmp", exist_ok=True)
    body, _ = _curl("-c", COOKIE_FILE, f"{BASE_URL}/login")
    m = re.search(r'name="_token"\s+value="([^"]+)"', body)
    csrf = m.group(1) if m else ""
    _curl(
        "-b", COOKIE_FILE, "-c", COOKIE_FILE,
        "-L",  # follow the post-login redirect so session cookie is set
        "-X", "POST",
        "-H", "Content-Type: application/x-www-form-urlencoded",
        "-d", f"_token={csrf}&email={ADMIN_EMAIL}&password={ADMIN_PASS}",
        f"{BASE_URL}/login"
    )
    print(f"✓ authenticated as {ADMIN_EMAIL}")


def get_routes() -> list[dict]:
    """Pull 200-status routes from the last authenticated smoke test results.
    Supports both legacy format (data['results'][]['status_code']) and
    current format (data['categories']['working'][]['status']).
    """
    results_path = RESULTS_DIR + "smoke_test_authenticated_results.json"
    if not os.path.exists(results_path):
        print(f"! No results file at {results_path}")
        return []
    with open(results_path) as f:
        data = json.load(f)
    routes = []
    # Current format: data['categories']['working'] with str status
    for category, entries in data.get("categories", {}).items():
        if not isinstance(entries, list):
            continue
        for r in entries:
            if not isinstance(r, dict):
                continue
            status = str(r.get("status", r.get("status_code", "")))
            if status == "200":
                routes.append({"uri": r["uri"], "name": r.get("name", "")})
    # Legacy format fallback
    if not routes:
        for r in data.get("results", []):
            if not isinstance(r, dict):
                continue
            if r.get("status_code") == 200 or str(r.get("status", "")) == "200":
                routes.append({"uri": r["uri"], "name": r.get("name", "")})
    return routes


# ---------------------------------------------------------------------------
# MAIN
# ---------------------------------------------------------------------------

def main():
    print("=" * 72)
    print("View Content Smoke Test")
    print(f"Base URL : {BASE_URL}")
    print(f"Started  : {datetime.now().isoformat()}")
    print("=" * 72)

    m0 = wait_for_cool("startup")
    login()
    time.sleep(1)

    routes = get_routes()
    if not routes:
        print("! No 200-routes found in smoke_test_authenticated_results.json")
        sys.exit(1)
    print(f"\nTesting content quality for {len(routes)} routes …\n")

    all_results = []
    grade_counts = {"RICH": 0, "PARTIAL": 0, "THIN": 0, "EMPTY": 0, "ERROR": 0}
    check_every = 8   # hw check every N requests

    for idx, route in enumerate(routes):
        if idx % check_every == 0:
            hw = wait_for_cool(f"{idx}/{len(routes)}")

        uri = route["uri"].lstrip("/")
        url = f"{BASE_URL}/{uri}"

        t0 = time.time()
        body, rc = _curl("-b", COOKIE_FILE, url, timeout=20)
        elapsed = time.time() - t0

        # Get final status from any redirect
        status_m = re.search(r'^HTTP/\S+\s+(\d+)', body, re.MULTILINE)
        if not status_m:
            # plain curl without -D -, body is html directly
            status_code = 200 if len(body) > 100 else 0
        else:
            sc_all = re.findall(r'^HTTP/\S+\s+(\d+)', body, re.MULTILINE)
            # body may include headers if we used -D - ; here we didn't, so this is html
            status_code = 200

        if rc == -1 or len(body) < 50:
            result = {
                "uri": uri, "name": route["name"],
                "grade": "ERROR", "score": -1, "reasons": ["timeout_or_empty"],
                "html_len": 0, "elapsed": elapsed,
            }
            grade_counts["ERROR"] += 1
        else:
            analysis = analyze_html(body)
            result = {
                "uri": uri,
                "name": route["name"],
                "elapsed": round(elapsed, 2),
                **analysis,
            }
            grade_counts[analysis["grade"]] += 1

        all_results.append(result)
        icon = {"RICH": "★", "PARTIAL": "◐", "THIN": "○", "EMPTY": "·", "ERROR": "✗"}.get(result["grade"], "?")
        print(f"  {icon} [{result['grade']:7}] {uri[:55]:<55} ({result.get('html_len',0)//1024}KB {result['elapsed']:.1f}s)")
        if result.get("finance_numbers"):
            print(f"         💰 {result['finance_numbers'][:3]}")
        if result.get("empty_states"):
            print(f"         ⚠  {result['empty_states'][0][:60]}")

        time.sleep(REQ_DELAY)

    # ── Save ──────────────────────────────────────────────────────────────
    out_path = RESULTS_DIR + "content_smoke_results.json"
    with open(out_path, "w") as f:
        json.dump({
            "generated": datetime.now().isoformat(),
            "base_url": BASE_URL,
            "total": len(all_results),
            "grade_distribution": grade_counts,
            "results": all_results,
        }, f, indent=2, default=str)
    print(f"\n✓ Saved: {out_path}")

    # ── Summary report ────────────────────────────────────────────────────
    summary_path = RESULTS_DIR + "content_smoke_summary.txt"
    with open(summary_path, "w") as f:
        f.write(f"View Content Smoke Test\nGenerated: {datetime.now().isoformat()}\n")
        f.write(f"Total routes tested: {len(all_results)}\n\n")

        for grade in ["RICH", "PARTIAL", "THIN", "EMPTY", "ERROR"]:
            rows = [r for r in all_results if r["grade"] == grade]
            f.write(f"{'='*72}\n{grade} ({len(rows)}):\n{'─'*72}\n")
            for r in rows:
                nums = r.get("finance_numbers", [])[:3]
                reasons = ", ".join(r.get("reasons", []))
                f.write(f"  [{r['elapsed']:.1f}s {r.get('html_len',0)//1024}KB] /{r['uri']}\n")
                if nums:
                    f.write(f"    $ {nums}\n")
                if r.get("empty_states"):
                    f.write(f"    ⚠ {r['empty_states'][0][:80]}\n")
                if reasons:
                    f.write(f"    → {reasons}\n")
            f.write("\n")

    print(f"✓ Summary: {summary_path}")

    # ── Terminal summary ──────────────────────────────────────────────────
    print("\n" + "=" * 72)
    print("CONTENT QUALITY SUMMARY")
    print("=" * 72)
    total = len(all_results)
    for grade, count in grade_counts.items():
        bar = "█" * (count * 40 // max(total, 1))
        print(f"  {grade:8} {count:4}  {bar}")

    # Views needing attention
    empties = [r for r in all_results if r["grade"] in ("EMPTY", "THIN")]
    if empties:
        print(f"\nViews with thin/empty content ({len(empties)}):")
        for r in empties[:20]:
            print(f"  [{r['grade']}] /{r['uri']}")
    print()


if __name__ == "__main__":
    main()
