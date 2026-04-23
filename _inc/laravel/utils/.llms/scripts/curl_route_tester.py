#!/usr/bin/env python3
"""
Curl Route Tester — hit every Laravel route and report HTTP status codes.

Usage:
    python3 curl_route_tester.py [--base-url URL] [--parallel N] [--timeout S]
                                 [--methods GET,POST] [--only-failures]
                                 [--report FILE] [--cookie-jar FILE]

The script:
1. Runs `php artisan route:list` to discover all routes
2. Authenticates via POST /login with configurable credentials
3. Curls every parameterless GET route (and optionally POST/PUT/DELETE)
4. For parameterized routes, substitutes {id} with '1', {slug} with 'test', etc.
5. Reports status codes, timing, and failures
6. Writes a JSON report file
"""

import argparse
import json
import os
import re
import subprocess
import sys
import time
from concurrent.futures import ThreadPoolExecutor, as_completed
from dataclasses import dataclass, field, asdict
from pathlib import Path
from typing import Optional
from urllib.parse import urljoin

LARAVEL_ROOT = Path(__file__).resolve().parents[3] / "laravel"

PARAM_DEFAULTS = {
    "id": "1",
    "uid": "1",
    "slug": "test",
    "lang": "en",
    "type": "default",
    "name": "test",
    "key": "test",
    "code": "test",
    "flag": "1",
    "template": "test",
    "template_name": "test",
    "month": "01",
    "year": "2024",
    "eid": "1",
    "session": "test",
    "date": "2024-01-01",
    "status": "active",
}

SKIP_PREFIXES = (
    "_debugbar", "_ignition", "sanctum", ".well-known",
    "broadcasting", "chatify", "api/",
)


@dataclass
class RouteResult:
    method: str
    uri: str
    name: str
    status_code: int = 0
    elapsed_ms: int = 0
    error: str = ""
    skipped: bool = False
    skip_reason: str = ""


def parse_route_list(laravel_root: Path) -> list[tuple[str, str, str]]:
    """Run artisan route:list and parse output."""
    result = subprocess.run(
        ["php", "artisan", "route:list"],
        capture_output=True, text=True, cwd=str(laravel_root),
        timeout=60
    )
    routes = []
    for line in result.stdout.splitlines():
        line = line.strip()
        if not line:
            continue
        m = re.match(r"^([\w|]+)\s+(\S+)\s+(.*)", line)
        if not m:
            continue
        methods_str = m.group(1)
        uri = m.group(2)
        rest = m.group(3).strip()
        name = ""
        if "›" in rest:
            name_part = rest.split("›")[0].strip().rstrip(".")
            if name_part and "\\" not in name_part and "::" not in name_part:
                name = name_part
        routes.append((methods_str, uri, name))
    return routes


def substitute_params(uri: str) -> str:
    """Replace {param} placeholders with reasonable defaults."""
    def replacer(m):
        param = m.group(1).rstrip("?")
        return PARAM_DEFAULTS.get(param, "1")
    return re.sub(r"\{(\w+\??)\}", replacer, uri)


def authenticate(base_url: str, email: str, password: str,
                 cookie_jar: str) -> bool:
    """Login and store session cookies."""
    login_url = urljoin(base_url, "/login")
    r = subprocess.run(
        ["curl", "-s", "-c", cookie_jar, login_url],
        capture_output=True, text=True, timeout=15
    )
    csrf_match = re.search(r'name="_token" value="([^"]+)"', r.stdout)
    if not csrf_match:
        print("ERROR: Could not find CSRF token on login page")
        return False
    csrf = csrf_match.group(1)
    r2 = subprocess.run(
        ["curl", "-s", "-o", "/dev/null", "-w", "%{http_code}",
         "-c", cookie_jar, "-b", cookie_jar,
         "-X", "POST",
         "-d", f"_token={csrf}&email={email}&password={password}",
         login_url],
        capture_output=True, text=True, timeout=15
    )
    code = r2.stdout.strip()
    if code == "302":
        return True
    print(f"WARNING: Login returned {code} (expected 302)")
    return False


def curl_route(base_url: str, method: str, uri: str, name: str,
               cookie_jar: str, timeout: int) -> RouteResult:
    """Curl a single route and return the result."""
    result = RouteResult(method=method, uri=uri, name=name)
    for prefix in SKIP_PREFIXES:
        if uri.lstrip("/").startswith(prefix):
            result.skipped = True
            result.skip_reason = f"skip prefix: {prefix}"
            return result
    resolved_uri = substitute_params(uri)
    url = urljoin(base_url, "/" + resolved_uri.lstrip("/"))
    curl_method = method.split("|")[0]
    cmd = [
        "curl", "-s", "-o", "/dev/null",
        "-w", "%{http_code}",
        "-b", cookie_jar,
        "-X", curl_method,
        "--max-time", str(timeout),
    ]
    if curl_method in ("POST", "PUT", "PATCH", "DELETE"):
        csrf_cmd = subprocess.run(
            ["grep", "XSRF-TOKEN", cookie_jar],
            capture_output=True, text=True
        )
        csrf_val = ""
        for c_line in csrf_cmd.stdout.splitlines():
            if "XSRF-TOKEN" in c_line:
                parts = c_line.split()
                if parts:
                    csrf_val = parts[-1]
        if csrf_val:
            cmd.extend(["-H", f"X-XSRF-TOKEN: {csrf_val}"])
        cmd.extend(["-d", ""])
    cmd.append(url)
    start = time.monotonic()
    try:
        r = subprocess.run(cmd, capture_output=True, text=True, timeout=timeout + 5)
        elapsed = int((time.monotonic() - start) * 1000)
        result.elapsed_ms = elapsed
        code_str = r.stdout.strip()
        result.status_code = int(code_str) if code_str.isdigit() else 0
    except subprocess.TimeoutExpired:
        result.error = "timeout"
        result.elapsed_ms = timeout * 1000
    except Exception as e:
        result.error = str(e)
    return result


def main():
    parser = argparse.ArgumentParser(description="Curl test all Laravel routes")
    parser.add_argument("--base-url", default="http://127.0.0.1:8787")
    parser.add_argument("--parallel", type=int, default=4)
    parser.add_argument("--timeout", type=int, default=10)
    parser.add_argument("--methods", default="GET")
    parser.add_argument("--only-failures", action="store_true")
    parser.add_argument("--report", default="/tmp/curl_route_report.json")
    parser.add_argument("--cookie-jar", default="/tmp/erp_cookies.txt")
    parser.add_argument("--email",
                        default="u_1ecb6d5a-e2c5-4961-af3b-0ad83f9d259c@test.local")
    parser.add_argument("--password", default="Admin@1234")
    parser.add_argument("--skip-auth", action="store_true")
    parser.add_argument("--laravel-root", default=str(LARAVEL_ROOT))
    args = parser.parse_args()

    allowed_methods = set(args.methods.upper().split(","))
    laravel_root = Path(args.laravel_root)

    print(f"Parsing routes from {laravel_root}...")
    all_routes = parse_route_list(laravel_root)
    print(f"Found {len(all_routes)} routes")

    if not args.skip_auth:
        print(f"Authenticating as {args.email}...")
        if not authenticate(args.base_url, args.email, args.password,
                            args.cookie_jar):
            print("Authentication failed — continuing with unauthenticated tests")

    test_routes = []
    for methods_str, uri, name in all_routes:
        for wanted in allowed_methods:
            if wanted in methods_str:
                test_routes.append((methods_str, uri, name))
                break
    print(f"Testing {len(test_routes)} routes (methods: {args.methods})")

    results: list[RouteResult] = []
    with ThreadPoolExecutor(max_workers=args.parallel) as pool:
        futures = {}
        for methods_str, uri, name in test_routes:
            f = pool.submit(curl_route, args.base_url, methods_str, uri, name,
                            args.cookie_jar, args.timeout)
            futures[f] = (methods_str, uri, name)

        done = 0
        for future in as_completed(futures):
            done += 1
            r = future.result()
            results.append(r)
            if done % 50 == 0:
                print(f"  Progress: {done}/{len(test_routes)}")

    results.sort(key=lambda r: (r.status_code, r.uri))

    codes = {}
    for r in results:
        if r.skipped:
            codes["SKIP"] = codes.get("SKIP", 0) + 1
        else:
            codes[r.status_code] = codes.get(r.status_code, 0) + 1

    print(f"\n{'='*60}")
    print("RESULTS SUMMARY")
    print(f"{'='*60}")
    for code in sorted(codes.keys(), key=lambda x: str(x)):
        print(f"  {code}: {codes[code]} routes")

    failures_5xx = [r for r in results if not r.skipped and r.status_code >= 500]
    failures_0 = [r for r in results if not r.skipped and r.status_code == 0]

    if failures_5xx:
        print(f"\n5xx ERRORS ({len(failures_5xx)}):")
        for r in failures_5xx:
            print(f"  {r.status_code} {r.method} /{r.uri} [{r.name}] ({r.elapsed_ms}ms)")

    if failures_0:
        print(f"\nTIMEOUT/ERROR ({len(failures_0)}):")
        for r in failures_0:
            print(f"  {r.method} /{r.uri} [{r.name}] err={r.error}")

    if not args.only_failures:
        redirects = [r for r in results if not r.skipped and 300 <= r.status_code < 400]
        if redirects:
            print(f"\n3xx REDIRECTS ({len(redirects)}):")
            for r in redirects[:20]:
                print(f"  {r.status_code} {r.method} /{r.uri} [{r.name}]")
            if len(redirects) > 20:
                print(f"  ... and {len(redirects) - 20} more")

        not_found = [r for r in results if not r.skipped and r.status_code == 404]
        if not_found:
            print(f"\n404 NOT FOUND ({len(not_found)}):")
            for r in not_found[:20]:
                print(f"  {r.method} /{r.uri} [{r.name}]")
            if len(not_found) > 20:
                print(f"  ... and {len(not_found) - 20} more")

    report_data = {
        "timestamp": time.strftime("%Y-%m-%dT%H:%M:%S"),
        "base_url": args.base_url,
        "total_routes": len(all_routes),
        "tested": len(test_routes),
        "summary": {str(k): v for k, v in codes.items()},
        "failures_5xx": [asdict(r) for r in failures_5xx],
        "failures_timeout": [asdict(r) for r in failures_0],
        "results": [asdict(r) for r in results],
    }
    with open(args.report, "w") as f:
        json.dump(report_data, f, indent=2)
    print(f"\nFull report saved to {args.report}")


if __name__ == "__main__":
    main()
