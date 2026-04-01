#!/usr/bin/env python3
"""
▓ Roleplay: CISO — GDPR / LGPD Compliance Scanner ▓
Verifica conformidade com GDPR/LGPD em respostas HTTP e código
"""
# PULL REQUEST START
import sys
import json
import re
import urllib.request
import urllib.error

TARGET = sys.argv[1] if len(sys.argv) > 1 else "http://127.0.0.1:8000"

PII_PATTERNS = {
    "email": r"[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}",
    "cpf": r"\d{3}\.\d{3}\.\d{3}-\d{2}",
    "cnpj": r"\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2}",
    "phone_br": r"\(\d{2}\)\s?\d{4,5}-\d{4}",
    "credit_card": r"\b\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4}\b",
    "ip_address": r"\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b",
    "jwt_token": r"eyJ[a-zA-Z0-9_-]+\.eyJ[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+",
}

GDPR_REQUIREMENTS = {
    "consent_mechanism": {
        "description": "Mecanismo de consentimento para coleta de dados",
        "check_headers": [],
        "check_body": ["consent", "consentimento", "aceitar", "agree", "opt-in"],
    },
    "right_to_erasure": {
        "description": "Endpoint para exclusão de dados pessoais",
        "endpoints": ["/api/users/me", "/api/account/delete", "/api/gdpr/erasure"],
        "method": "DELETE",
    },
    "data_portability": {
        "description": "Endpoint para exportação de dados do usuário",
        "endpoints": ["/api/users/me/export", "/api/gdpr/export", "/api/account/data"],
        "method": "GET",
    },
    "privacy_policy": {
        "description": "Página de política de privacidade acessível",
        "endpoints": ["/privacy", "/privacy-policy", "/politica-privacidade"],
        "method": "GET",
    },
    "cookie_consent": {
        "description": "Banner de consentimento de cookies",
        "check_body": ["cookie-consent", "cookie-banner", "cookieConsent"],
    },
    "data_minimization": {
        "description": "Coleta apenas dados necessários nos formulários",
        "check_body": ["required", "optional", "obrigatório"],
    },
}

PRIVACY_HEADERS = {
    "X-Content-Type-Options": "nosniff",
    "X-Frame-Options": "DENY",
    "Referrer-Policy": "strict-origin-when-cross-origin",
    "Permissions-Policy": None,
    "Cache-Control": "no-store",
}


def scan_pii_in_response(url: str, endpoint: str) -> list:
    """Verifica se respostas HTTP contêm PII sem máscara."""
    findings = []
    try:
        req = urllib.request.Request(url + endpoint, headers={"User-Agent": "Mozilla/5.0"})
        resp = urllib.request.urlopen(req, timeout=10)
        body = resp.read().decode("utf-8", errors="replace")
        for pii_type, pattern in PII_PATTERNS.items():
            matches = re.findall(pattern, body)
            if matches:
                findings.append({
                    "endpoint": endpoint,
                    "pii_type": pii_type,
                    "count": len(matches),
                    "sample": matches[0][:20] + "...",
                })
    except (urllib.error.URLError, urllib.error.HTTPError, OSError):
        pass
    return findings


def check_privacy_headers(url: str) -> dict:
    """Verifica headers de privacidade."""
    result = {"present": [], "missing": [], "incorrect": []}
    try:
        req = urllib.request.Request(url, headers={"User-Agent": "Mozilla/5.0"})
        resp = urllib.request.urlopen(req, timeout=10)
        headers = {k.lower(): v for k, v in resp.headers.items()}
        for header, expected in PRIVACY_HEADERS.items():
            h_lower = header.lower()
            if h_lower not in headers:
                result["missing"].append(header)
            elif expected and headers[h_lower] != expected:
                result["incorrect"].append({"header": header, "got": headers[h_lower], "expected": expected})
            else:
                result["present"].append(header)
    except (urllib.error.URLError, urllib.error.HTTPError, OSError):
        result["error"] = "Não foi possível conectar"
    return result


def check_gdpr_endpoints(url: str) -> dict:
    """Verifica se endpoints GDPR existem e respondem."""
    results = {}
    for req_name, config in GDPR_REQUIREMENTS.items():
        if "endpoints" not in config:
            results[req_name] = {"status": "manual_check", "description": config["description"]}
            continue
        found = False
        for ep in config["endpoints"]:
            try:
                r = urllib.request.Request(
                    url + ep,
                    method=config.get("method", "GET"),
                    headers={"User-Agent": "Mozilla/5.0"},
                )
                resp = urllib.request.urlopen(r, timeout=5)
                if resp.status < 500:
                    found = True
                    break
            except urllib.error.HTTPError as e:
                if e.code < 500:
                    found = True
                    break
            except (urllib.error.URLError, OSError):
                pass
        results[req_name] = {
            "status": "found" if found else "missing",
            "description": config["description"],
        }
    return results


def main():
    print("[CISO] GDPR/LGPD Compliance Scanner")
    print(f"Alvo: {TARGET}")
    print("=" * 55)

    # PII scan
    pii_endpoints = ["/api/users", "/api/profile", "/api/customers", "/api/employees", "/api/reports"]
    all_pii = []
    for ep in pii_endpoints:
        all_pii.extend(scan_pii_in_response(TARGET, ep))
    print(f"\nPII exposto em respostas: {len(all_pii)}")
    for p in all_pii:
        print(f"  [!] {p['endpoint']}: {p['pii_type']} ({p['count']}x)")

    # Privacy headers
    headers = check_privacy_headers(TARGET)
    print(f"\nHeaders de privacidade:")
    print(f"  Presentes: {len(headers['present'])}")
    print(f"  Ausentes: {len(headers['missing'])}")
    for h in headers["missing"]:
        print(f"    - {h}")

    # GDPR endpoints
    gdpr = check_gdpr_endpoints(TARGET)
    print(f"\nRequisitos GDPR/LGPD:")
    for name, result in gdpr.items():
        status = "[✓]" if result["status"] == "found" else "[✗]" if result["status"] == "missing" else "[?]"
        print(f"  {status} {result['description']}")

    score = (
        max(0, 30 - len(all_pii) * 5) +
        len(headers["present"]) * 10 +
        sum(10 for r in gdpr.values() if r["status"] == "found")
    )
    grade = "A" if score >= 80 else "B" if score >= 60 else "C" if score >= 40 else "D" if score >= 20 else "F"

    print(f"\nScore: {score}")
    print(f"Grade: {grade}")
    print(json.dumps({"pii_leaks": len(all_pii), "headers": headers, "gdpr": gdpr, "score": score, "grade": grade}, indent=2, default=str))
    print("[CISO] GDPR/LGPD Scan completo")


if __name__ == "__main__":
    main()
# PULL REQUEST END
