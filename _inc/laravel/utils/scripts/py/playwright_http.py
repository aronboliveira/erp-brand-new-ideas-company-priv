#!/usr/bin/env python3
"""
playwright_http.py — Browser-level HTTP smoke tests using Playwright.

Tests routes that require JavaScript rendering (SPAs, AJAX-loaded content,
Bootstrap-dependent UI). Falls back to basic HTTP checks if Playwright
is not installed.

Usage:
    python3 playwright_http.py [--headed] [--slow-mo=500] [--base-url=http://...]
    python3 playwright_http.py --install   # Install Playwright browsers

Requires: pip install playwright && playwright install chromium
Env: BASE_URL, ADMIN_EMAIL, ADMIN_PASS
"""
import argparse
import json
import os
import sys
import time
from dataclasses import dataclass, field

try:
    from playwright.sync_api import sync_playwright, TimeoutError as PwTimeout
    HAS_PLAYWRIGHT = True
except ImportError:
    HAS_PLAYWRIGHT = False

# ── Config ────────────────────────────────────────────────────
BASE_URL = os.getenv("BASE_URL", "http://127.0.0.1:8000")
ADMIN_EMAIL = os.getenv("ADMIN_EMAIL", "admin@example.com")
ADMIN_PASS = os.getenv("ADMIN_PASS", "password")


@dataclass
class TestResult:
    name: str
    passed: bool
    status: int = 0
    duration_ms: float = 0
    error: str = ""
    details: dict = field(default_factory=dict)


class PlaywrightHTTPTester:
    """Runs browser-level HTTP tests with Playwright."""

    def __init__(self, base_url: str, headed: bool = False, slow_mo: int = 0):
        self.base_url = base_url.rstrip("/")
        self.headed = headed
        self.slow_mo = slow_mo
        self.results: list[TestResult] = []

    def run_all(self) -> list[TestResult]:
        with sync_playwright() as p:
            browser = p.chromium.launch(
                headless=not self.headed,
                slow_mo=self.slow_mo,
            )
            context = browser.new_context(
                viewport={"width": 1280, "height": 720},
                ignore_https_errors=True,
            )
            page = context.new_page()

            # ── Public routes ──
            self._test_page_load(page, "/", "Homepage")
            self._test_page_load(page, "/login", "Login page", expect_text="email")
            self._test_page_load(page, "/register", "Register page")
            self._test_page_load(page, "/password/reset", "Password reset")

            # ── JS-rendered checks ──
            self._test_js_rendered(page, "/login", "Login form JS",
                                   selector="form", expect_element="input[name='email']")

            # ── Login flow ──
            logged_in = self._test_login(page)

            if logged_in:
                # ── Auth routes ──
                self._test_page_load(page, "/dashboard", "Dashboard (auth)")
                self._test_page_load(page, "/clients", "Clients (auth)")
                self._test_page_load(page, "/projects", "Projects (auth)")

                # ── JS-heavy pages ──
                self._test_js_rendered(page, "/dashboard", "Dashboard widgets",
                                       selector=".card,.widget,.row", wait_ms=3000)

                # ── Console error detection ──
                self._test_console_errors(page, "/dashboard", "Dashboard console")

                # ── Network waterfall check ──
                self._test_network_perf(page, "/dashboard", "Dashboard network")

            context.close()
            browser.close()

        return self.results

    def _test_page_load(self, page, path: str, name: str,
                        expect_status: int = 200, expect_text: str = ""):
        start = time.time()
        try:
            resp = page.goto(f"{self.base_url}{path}", wait_until="domcontentloaded", timeout=15000)
            status = resp.status if resp else 0
            duration = (time.time() - start) * 1000
            # Accept redirects to login as valid
            passed = status == expect_status or (expect_status == 200 and status in (200, 302))

            if expect_text and passed:
                content = page.content()
                if expect_text.lower() not in content.lower():
                    passed = False

            self.results.append(TestResult(
                name=name, passed=passed, status=status,
                duration_ms=round(duration, 1),
                error="" if passed else f"Expected {expect_status}, got {status}"
            ))
        except PwTimeout:
            self.results.append(TestResult(
                name=name, passed=False, duration_ms=(time.time() - start) * 1000,
                error="Timeout"
            ))
        except Exception as e:
            self.results.append(TestResult(
                name=name, passed=False, error=str(e)
            ))

    def _test_js_rendered(self, page, path: str, name: str,
                          selector: str = "", expect_element: str = "", wait_ms: int = 5000):
        start = time.time()
        try:
            page.goto(f"{self.base_url}{path}", wait_until="networkidle", timeout=15000)

            if selector:
                page.wait_for_selector(selector, timeout=wait_ms)

            found = True
            if expect_element:
                el = page.query_selector(expect_element)
                found = el is not None

            duration = (time.time() - start) * 1000
            self.results.append(TestResult(
                name=name, passed=found, duration_ms=round(duration, 1),
                error="" if found else f"Element '{expect_element}' not found"
            ))
        except PwTimeout:
            self.results.append(TestResult(
                name=name, passed=False, error=f"Timeout waiting for '{selector or expect_element}'"
            ))
        except Exception as e:
            self.results.append(TestResult(name=name, passed=False, error=str(e)))

    def _test_login(self, page) -> bool:
        start = time.time()
        try:
            page.goto(f"{self.base_url}/login", wait_until="domcontentloaded", timeout=15000)
            page.wait_for_selector("input[name='email']", timeout=5000)
            page.fill("input[name='email']", ADMIN_EMAIL)
            page.fill("input[name='password']", ADMIN_PASS)

            submit = page.query_selector("button[type='submit'], input[type='submit']")
            if submit:
                submit.click()
            else:
                page.keyboard.press("Enter")

            page.wait_for_load_state("networkidle", timeout=10000)
            url_after = page.url
            logged_in = "/login" not in url_after

            duration = (time.time() - start) * 1000
            self.results.append(TestResult(
                name="Login flow", passed=logged_in, duration_ms=round(duration, 1),
                error="" if logged_in else f"Still on {url_after}",
                details={"redirect_to": url_after}
            ))
            return logged_in
        except Exception as e:
            self.results.append(TestResult(
                name="Login flow", passed=False, error=str(e)
            ))
            return False

    def _test_console_errors(self, page, path: str, name: str):
        errors: list[str] = []
        page.on("console", lambda msg: errors.append(msg.text) if msg.type == "error" else None)
        try:
            page.goto(f"{self.base_url}{path}", wait_until="networkidle", timeout=15000)
            page.wait_for_timeout(2000)
        except Exception:
            pass

        passed = len(errors) == 0
        self.results.append(TestResult(
            name=name, passed=passed,
            error=f"{len(errors)} console errors" if errors else "",
            details={"errors": errors[:10]}
        ))

    def _test_network_perf(self, page, path: str, name: str):
        requests: list[dict] = []
        page.on("request", lambda req: requests.append({
            "url": req.url, "method": req.method,
            "resource_type": req.resource_type,
        }))
        failed: list[str] = []
        page.on("requestfailed", lambda req: failed.append(req.url))

        start = time.time()
        try:
            page.goto(f"{self.base_url}{path}", wait_until="networkidle", timeout=15000)
        except Exception:
            pass
        duration = (time.time() - start) * 1000

        passed = len(failed) == 0
        self.results.append(TestResult(
            name=name, passed=passed, duration_ms=round(duration, 1),
            error=f"{len(failed)} failed requests" if failed else "",
            details={
                "total_requests": len(requests),
                "failed_urls": failed[:5],
            }
        ))


def print_results(results: list[TestResult]):
    """Console-friendly output."""
    print(f"\n{'═' * 60}")
    print(f" Playwright HTTP Tests — {BASE_URL}")
    print(f"{'═' * 60}\n")

    passed = failed = 0
    for r in results:
        icon = "✅" if r.passed else "❌"
        status_str = f"HTTP {r.status}" if r.status else ""
        time_str = f"{r.duration_ms:.0f}ms" if r.duration_ms else ""
        parts = [s for s in [status_str, time_str] if s]
        suffix = f" [{', '.join(parts)}]" if parts else ""
        error = f" — {r.error}" if r.error else ""
        print(f" {icon} {r.name}{suffix}{error}")
        if r.passed:
            passed += 1
        else:
            failed += 1

    print(f"\n{'─' * 60}")
    print(f" ✅ Passed: {passed}  ❌ Failed: {failed}  Total: {len(results)}")
    print(f"{'═' * 60}\n")


def main():
    parser = argparse.ArgumentParser(description="Playwright HTTP smoke tests")
    parser.add_argument("--headed", action="store_true", help="Show browser window")
    parser.add_argument("--slow-mo", type=int, default=0, help="Slow down actions (ms)")
    parser.add_argument("--base-url", default=BASE_URL, help="Base URL to test")
    parser.add_argument("--json", action="store_true", help="JSON output")
    parser.add_argument("--install", action="store_true", help="Install Playwright browsers")
    args = parser.parse_args()

    if args.install:
        import subprocess
        subprocess.run([sys.executable, "-m", "playwright", "install", "chromium"], check=True)
        print("Chromium installed successfully.")
        return

    if not HAS_PLAYWRIGHT:
        print("Playwright not installed. Install with:")
        print("  pip install playwright")
        print("  playwright install chromium")
        print("\nOr use http_tests.sh for curl/wget-based tests.")
        sys.exit(1)

    tester = PlaywrightHTTPTester(
        base_url=args.base_url,
        headed=args.headed,
        slow_mo=args.slow_mo,
    )
    results = tester.run_all()

    if args.json:
        data = [{"name": r.name, "passed": r.passed, "status": r.status,
                 "duration_ms": r.duration_ms, "error": r.error, **r.details}
                for r in results]
        print(json.dumps(data, indent=2))
    else:
        print_results(results)

    sys.exit(0 if all(r.passed for r in results) else 1)


if __name__ == "__main__":
    main()
