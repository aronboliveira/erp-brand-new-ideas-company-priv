#!/usr/bin/env python3
# ▓ Roleplay: CISO — OWASP Compliance Audit
# Auditor executivo. Verifica compliance OWASP Top 10 do sistema.
# PULL REQUEST START
"""
Auditoria automatizada de compliance OWASP Top 10.
Uso: python3 compliance_audit.py [URL]
"""
import json
import os
import sys

import requests

BASE = os.environ.get("APP_URL", "http://127.0.0.1:8000")

# ── OWASP Top 10 (2021) Checks ──────────────────────────
CHECKS = [
    {
        "id": "A01",
        "name": "Broken Access Control",
        "tests": [
            ("GET protegido sem auth", "/dashboard", "GET", None, [302, 401, 403]),
            ("Admin sem auth", "/settings", "GET", None, [302, 401, 403]),
        ],
    },
    {
        "id": "A02",
        "name": "Cryptographic Failures",
        "tests": [
            ("HSTS presente", "/", "HEADER", "Strict-Transport-Security", None),
        ],
    },
    {
        "id": "A03",
        "name": "Injection",
        "tests": [
            ("SQLi básico", "/search?q=' OR 1=1--", "GET", None, [200, 302, 403]),
        ],
    },
    {
        "id": "A05",
        "name": "Security Misconfiguration",
        "tests": [
            ("X-Powered-By ausente", "/", "HEADER-ABSENT", "X-Powered-By", None),
            ("Server header ausente", "/", "HEADER-ABSENT", "Server", None),
        ],
    },
    {
        "id": "A07",
        "name": "Identification and Authentication Failures",
        "tests": [
            ("CSRF em login", "/login", "POST", None, [419, 302, 403]),
        ],
    },
    {
        "id": "A09",
        "name": "Security Logging and Monitoring Failures",
        "tests": [
            ("Login page acessível", "/login", "GET", None, [200]),
        ],
    },
]


def run_check(test_tuple: tuple, target: str) -> dict:
    """Executa um check individual."""
    label, path, method, param, expected = test_tuple
    url = f"{target}{path}"
    result = {"label": label, "path": path, "method": method}

    try:
        if method == "GET":
            r = requests.get(url, timeout=10, allow_redirects=False)
            result["status"] = r.status_code
            result["pass"] = r.status_code in (expected or [200])

        elif method == "POST":
            r = requests.post(
                url,
                data={"test": "x"},
                timeout=10,
                allow_redirects=False,
            )
            result["status"] = r.status_code
            result["pass"] = r.status_code in (expected or [200])

        elif method == "HEADER":
            r = requests.get(url, timeout=10, allow_redirects=False)
            header_val = r.headers.get(param, "")
            result["header"] = param
            result["value"] = header_val
            result["pass"] = bool(header_val)

        elif method == "HEADER-ABSENT":
            r = requests.get(url, timeout=10, allow_redirects=False)
            header_val = r.headers.get(param, "")
            result["header"] = param
            result["value"] = header_val
            result["pass"] = not header_val

    except requests.RequestException as e:
        result["pass"] = False
        result["error"] = str(e)

    return result


def main():
    target = sys.argv[1] if len(sys.argv) > 1 else BASE
    print("[CISO] OWASP Compliance Audit v1.0")
    print(f"[CISO] Alvo: {target}")
    print("═" * 50)

    total_pass = 0
    total_fail = 0
    all_results = []

    for check in CHECKS:
        print(f"\n[{check['id']}] {check['name']}")
        for test_tuple in check["tests"]:
            result = run_check(test_tuple, target)
            result["owasp_id"] = check["id"]
            all_results.append(result)

            icon = "✓" if result["pass"] else "✗"
            status = result.get("status", result.get("value", "N/A"))
            print(f"  [{icon}] {result['label']} → {status}")

            if result["pass"]:
                total_pass += 1
            else:
                total_fail += 1

    total = total_pass + total_fail
    score = (total_pass * 100 // total) if total > 0 else 0

    print("\n" + "═" * 50)
    print(f"[CISO] {total_pass}/{total} checks passaram ({score}%)")

    if score >= 80:
        print("[CISO] Classificação: ADEQUADO")
    elif score >= 50:
        print("[CISO] Classificação: NECESSITA MELHORIA")
    else:
        print("[CISO] Classificação: NÃO CONFORME")

    report_path = "/tmp/ciso-owasp-compliance.json"
    with open(report_path, "w") as fp:
        json.dump(
            {
                "actor": "ciso",
                "tool": "compliance_audit",
                "target": target,
                "results": all_results,
                "score": score,
            },
            fp,
            indent=2,
        )
    print(f"[CISO] Relatório: {report_path}")


if __name__ == "__main__":
    main()
# PULL REQUEST END
