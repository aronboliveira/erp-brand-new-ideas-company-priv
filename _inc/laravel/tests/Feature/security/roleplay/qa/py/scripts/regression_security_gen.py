#!/usr/bin/env python3
"""
▓ Roleplay: QA — Regression Security Test Generator ▓
Gera testes de regressão de segurança baseados em vulnerabilidades conhecidas
"""
# PULL REQUEST START
import sys
import json
import urllib.request
import urllib.error
import urllib.parse

TARGET = sys.argv[1] if len(sys.argv) > 1 else "http://127.0.0.1:8000"

KNOWN_VULNS = [
    {
        "id": "CVE-FAKE-001",
        "type": "SQLi",
        "endpoint": "/api/search",
        "param": "q",
        "payload": "' OR '1'='1'--",
        "fixed_status": [400, 422, 403],
        "description": "SQLi via parâmetro de busca",
    },
    {
        "id": "CVE-FAKE-002",
        "type": "XSS",
        "endpoint": "/api/profile",
        "param": "name",
        "payload": "<script>alert(1)</script>",
        "fixed_status": [400, 422],
        "description": "XSS refletido no nome do perfil",
    },
    {
        "id": "CVE-FAKE-003",
        "type": "IDOR",
        "endpoint": "/api/users/999999",
        "param": None,
        "payload": None,
        "fixed_status": [403, 404],
        "description": "IDOR em endpoint de usuário",
    },
    {
        "id": "CVE-FAKE-004",
        "type": "Path Traversal",
        "endpoint": "/api/files",
        "param": "path",
        "payload": "../../etc/passwd",
        "fixed_status": [400, 403],
        "description": "Path traversal no download de arquivos",
    },
    {
        "id": "CVE-FAKE-005",
        "type": "CSRF",
        "endpoint": "/api/profile",
        "param": None,
        "payload": None,
        "fixed_status": [419, 403],
        "description": "CSRF em endpoint de atualização de perfil",
    },
    {
        "id": "CVE-FAKE-006",
        "type": "Open Redirect",
        "endpoint": "/redirect",
        "param": "url",
        "payload": "https://evil.test/phish",
        "fixed_status": [400, 403],
        "description": "Open redirect via parâmetro url",
    },
    {
        "id": "CVE-FAKE-007",
        "type": "Mass Assignment",
        "endpoint": "/api/users/1",
        "param": "is_admin",
        "payload": "true",
        "fixed_status": [400, 403, 422],
        "description": "Mass assignment de campo is_admin",
    },
    {
        "id": "CVE-FAKE-008",
        "type": "Rate Limit",
        "endpoint": "/api/login",
        "param": None,
        "payload": None,
        "fixed_status": [429],
        "description": "Endpoint de login sem rate limiting",
    },
]


def run_regression_test(url: str, vuln: dict) -> dict:
    """Executa um teste de regressão para uma vulnerabilidade conhecida."""
    result = {
        "id": vuln["id"],
        "type": vuln["type"],
        "description": vuln["description"],
        "status": "unknown",
        "http_code": 0,
    }
    try:
        if vuln["param"] and vuln["payload"]:
            if vuln["type"] in ("SQLi", "XSS", "Path Traversal", "Open Redirect"):
                full_url = f"{url}{vuln['endpoint']}?{urllib.parse.urlencode({vuln['param']: vuln['payload']})}"
                req = urllib.request.Request(full_url, method="GET")
            else:
                data = urllib.parse.urlencode({vuln["param"]: vuln["payload"]}).encode()
                req = urllib.request.Request(url + vuln["endpoint"], data=data, method="POST")
                req.add_header("Content-Type", "application/x-www-form-urlencoded")
        elif vuln["type"] == "CSRF":
            data = urllib.parse.urlencode({"name": "test"}).encode()
            req = urllib.request.Request(url + vuln["endpoint"], data=data, method="PUT")
            req.add_header("Content-Type", "application/x-www-form-urlencoded")
        elif vuln["type"] == "Rate Limit":
            req = urllib.request.Request(url + vuln["endpoint"], method="POST")
            req.add_header("Content-Type", "application/json")
        else:
            req = urllib.request.Request(url + vuln["endpoint"], method="GET")

        req.add_header("User-Agent", "QA-Regression/1.0")
        resp = urllib.request.urlopen(req, timeout=5)
        result["http_code"] = resp.status
    except urllib.error.HTTPError as e:
        result["http_code"] = e.code
    except (urllib.error.URLError, OSError):
        result["status"] = "error"
        return result

    if result["http_code"] in vuln["fixed_status"]:
        result["status"] = "fixed"
    elif result["http_code"] == 200:
        result["status"] = "REGRESSION"
    else:
        result["status"] = "check_manually"

    return result


def main():
    print("[QA] Regression Security Test Generator")
    print(f"Alvo: {TARGET}")
    print("=" * 50)

    print(f"\nVulnerabilidades para regressão: {len(KNOWN_VULNS)}")
    results = []

    for vuln in KNOWN_VULNS:
        r = run_regression_test(TARGET, vuln)
        results.append(r)
        if r["status"] == "fixed":
            print(f"  [✓] {r['id']}: {r['description']} — corrigido (HTTP {r['http_code']})")
        elif r["status"] == "REGRESSION":
            print(f"  [✗] {r['id']}: {r['description']} — REGRESSÃO! (HTTP {r['http_code']})")
        elif r["status"] == "error":
            print(f"  [?] {r['id']}: {r['description']} — erro de conexão")
        else:
            print(f"  [~] {r['id']}: {r['description']} — verificar (HTTP {r['http_code']})")

    fixed = sum(1 for r in results if r["status"] == "fixed")
    regressions = sum(1 for r in results if r["status"] == "REGRESSION")
    errors = sum(1 for r in results if r["status"] == "error")

    print(f"\n── Resumo ──")
    print(f"  Corrigidos: {fixed}")
    print(f"  Regressões: {regressions}")
    print(f"  Erros: {errors}")
    print(json.dumps({
        "total": len(results),
        "fixed": fixed,
        "regressions": regressions,
        "errors": errors,
    }, indent=2))
    print("[QA] Regression Test completo")


if __name__ == "__main__":
    main()
# PULL REQUEST END
