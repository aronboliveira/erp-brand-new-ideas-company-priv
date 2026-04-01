#!/usr/bin/env python3
# ▓ Roleplay: White Hat — OWASP HTTP Header Audit
# Metodológico. Verifica headers de segurança conforme OWASP.
# PULL REQUEST START
"""
Audita headers HTTP de segurança conforme OWASP Secure Headers Project.
Uso: python3 owasp_header_audit.py [URL]
"""
import json
import os
import sys

import requests

BASE = os.environ.get("APP_URL", "http://127.0.0.1:8000")

# Referência: OWASP Secure Headers Project
REQUIRED_HEADERS = {
    "X-Frame-Options": {
        "expected": ["DENY", "SAMEORIGIN"],
        "severity": "HIGH",
        "owasp": "Clickjacking Protection",
    },
    "X-Content-Type-Options": {
        "expected": ["nosniff"],
        "severity": "MEDIUM",
        "owasp": "MIME-type Sniffing Prevention",
    },
    "Strict-Transport-Security": {
        "expected": None,  # Qualquer valor — presença é suficiente
        "severity": "HIGH",
        "owasp": "HSTS — HTTP Strict Transport Security",
    },
    "Content-Security-Policy": {
        "expected": None,
        "severity": "HIGH",
        "owasp": "Content Security Policy",
    },
    "Referrer-Policy": {
        "expected": [
            "no-referrer",
            "strict-origin",
            "strict-origin-when-cross-origin",
            "same-origin",
        ],
        "severity": "LOW",
        "owasp": "Referrer Information Leakage",
    },
    "Permissions-Policy": {
        "expected": None,
        "severity": "LOW",
        "owasp": "Browser Feature Permissions",
    },
}

DANGEROUS_HEADERS = ["Server", "X-Powered-By", "X-AspNet-Version"]


def audit_headers(url: str) -> list[dict]:
    """Audita headers de segurança de uma URL."""
    try:
        r = requests.get(url, timeout=10, allow_redirects=True)
    except requests.ConnectionError:
        return [{"type": "ERROR", "message": f"Servidor inacessível: {url}"}]

    findings = []
    headers = r.headers

    # Verificar headers obrigatórios
    for name, spec in REQUIRED_HEADERS.items():
        value = headers.get(name)
        if value is None:
            findings.append(
                {
                    "type": "MISSING",
                    "header": name,
                    "severity": spec["severity"],
                    "owasp": spec["owasp"],
                }
            )
        elif spec["expected"] and value not in spec["expected"]:
            findings.append(
                {
                    "type": "WEAK",
                    "header": name,
                    "value": value,
                    "expected": spec["expected"],
                    "severity": spec["severity"],
                }
            )
        else:
            findings.append(
                {
                    "type": "OK",
                    "header": name,
                    "value": value,
                    "severity": spec["severity"],
                }
            )

    # Verificar headers que vazam informação
    for name in DANGEROUS_HEADERS:
        value = headers.get(name)
        if value:
            findings.append(
                {
                    "type": "INFO_LEAK",
                    "header": name,
                    "value": value,
                    "severity": "LOW",
                    "note": "Header expõe informação do servidor",
                }
            )

    return findings


def main():
    url = sys.argv[1] if len(sys.argv) > 1 else f"{BASE}/login"
    print("[WHITE-HAT] OWASP Header Audit v1.0")
    print(f"[WHITE-HAT] Alvo: {url}")
    print("═" * 50)

    findings = audit_headers(url)

    ok = sum(1 for f in findings if f["type"] == "OK")
    missing = sum(1 for f in findings if f["type"] == "MISSING")
    weak = sum(1 for f in findings if f["type"] == "WEAK")
    leaks = sum(1 for f in findings if f["type"] == "INFO_LEAK")

    for f in findings:
        icon = {"OK": "✓", "MISSING": "✗", "WEAK": "⚠", "INFO_LEAK": "ℹ"}.get(
            f["type"], "?"
        )
        severity = f.get("severity", "")
        header = f.get("header", "")
        value = f.get("value", "ausente")
        print(f"  [{icon}] [{severity:6}] {header}: {value}")

    print("═" * 50)
    print(
        f"[WHITE-HAT] Resumo: {ok} OK, {missing} ausentes, {weak} fracos, {leaks} info leaks"
    )

    # Saída JSON para automação
    report = {
        "url": url,
        "summary": {"ok": ok, "missing": missing, "weak": weak, "info_leaks": leaks},
        "findings": findings,
    }
    report_path = "/tmp/white-hat-header-audit.json"
    with open(report_path, "w") as fp:
        json.dump(report, fp, indent=2)
    print(f"[WHITE-HAT] Relatório: {report_path}")


if __name__ == "__main__":
    main()
# PULL REQUEST END
