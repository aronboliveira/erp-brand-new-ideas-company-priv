#!/usr/bin/env python3
# ▓ Roleplay: White Hat — Authentication Bypass Probe
# Pentester ético. Testa bypass de autenticação via headers e cookies.
# PULL REQUEST START
"""
Tenta bypass de autenticação via manipulação de headers, cookies, e tokens.
Uso: python3 auth_bypass_probe.py [URL]
"""
import json
import os
import sys

import requests

BASE = os.environ.get("APP_URL", "http://127.0.0.1:8000")

PROTECTED_ROUTES = ["/dashboard", "/invoices", "/customers"]

# Técnicas de bypass
BYPASS_TECHNIQUES = [
    {
        "name": "X-Forwarded-For localhost",
        "headers": {"X-Forwarded-For": "127.0.0.1"},
    },
    {
        "name": "X-Original-URL override",
        "headers": {"X-Original-URL": "/dashboard"},
    },
    {
        "name": "X-Rewrite-URL override",
        "headers": {"X-Rewrite-URL": "/dashboard"},
    },
    {
        "name": "X-Custom-IP-Authorization",
        "headers": {"X-Custom-IP-Authorization": "127.0.0.1"},
    },
    {
        "name": "Referer from internal",
        "headers": {"Referer": "http://127.0.0.1:8000/admin"},
    },
    {
        "name": "Accept: application/json (API bypass)",
        "headers": {"Accept": "application/json", "X-Requested-With": "XMLHttpRequest"},
    },
]


def test_bypass(url: str, technique: dict) -> dict:
    """Tenta acessar rota protegida com headers de bypass."""
    try:
        r = requests.get(
            url,
            headers=technique["headers"],
            timeout=10,
            allow_redirects=False,
        )
        # Se retorna 200 sem redirect, pode ser bypass
        bypassed = r.status_code == 200
        return {
            "technique": technique["name"],
            "url": url,
            "status": r.status_code,
            "bypassed": bypassed,
            "verdict": "VULN" if bypassed else "SAFE",
        }
    except requests.ConnectionError:
        return {
            "technique": technique["name"],
            "url": url,
            "status": 0,
            "bypassed": False,
            "verdict": "ERROR",
        }


def main():
    target = sys.argv[1] if len(sys.argv) > 1 else BASE
    print("[WHITE-HAT] Auth Bypass Probe v1.0")
    print(f"[WHITE-HAT] Alvo: {target}")
    print("═" * 50)

    results = []
    for route in PROTECTED_ROUTES:
        url = f"{target}{route}"
        print(f"\n[ROUTE] {route}")
        for technique in BYPASS_TECHNIQUES:
            result = test_bypass(url, technique)
            results.append(result)
            icon = "✗" if result["bypassed"] else "✓"
            print(
                f"  [{icon}] {technique['name']:40} → {result['status']} ({result['verdict']})"
            )

    print("\n" + "═" * 50)
    bypassed = sum(1 for r in results if r["bypassed"])
    total = len(results)
    print(f"[WHITE-HAT] {total} testes, {bypassed} bypasses encontrados")

    report_path = "/tmp/white-hat-auth-bypass.json"
    with open(report_path, "w") as fp:
        json.dump({"target": target, "results": results}, fp, indent=2)
    print(f"[WHITE-HAT] Relatório: {report_path}")


if __name__ == "__main__":
    main()
# PULL REQUEST END
