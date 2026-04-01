#!/usr/bin/env python3
# ▓ Roleplay: Green Hat — Session Cookie Dumper
# Copiado de tutorial "python steal session cookies"
# Iniciante. Pega cookies e tenta decodificar base64.
# PULL REQUEST START
"""
Dumpa cookies de sessão e tenta decodificar valores base64.
Uso: python3 session_dump.py [URL]
"""
import base64
import json
import os
import sys

import requests

BASE = os.environ.get("APP_URL", "http://127.0.0.1:8000")


def dump_cookies(url: str) -> dict:
    """Faz GET na URL e retorna todos os cookies."""
    s = requests.Session()
    try:
        r = s.get(url, timeout=10, allow_redirects=True)
    except requests.ConnectionError:
        print("[GREEN-HAT] Erro: servidor inacessível")
        return {}
    cookies = {}
    for c in s.cookies:
        cookies[c.name] = {
            "value": c.value,
            "domain": c.domain,
            "path": c.path,
            "secure": c.secure,
            "httponly": c.has_nonstandard_attr("HttpOnly")
            or c.has_nonstandard_attr("httponly"),
        }
    return cookies


def try_decode_base64(value: str) -> str | None:
    """Tenta decodificar base64 (como tutorial ensina)."""
    for encoding in [value, value + "=", value + "=="]:
        try:
            decoded = base64.b64decode(encoding).decode("utf-8", errors="replace")
            if decoded.isprintable() or "{" in decoded:
                return decoded
        except Exception:
            continue
    return None


def try_decode_jwt(value: str) -> dict | None:
    """Tenta decodificar JWT payload."""
    parts = value.split(".")
    if len(parts) != 3:
        return None
    try:
        padded = parts[1] + "=" * (4 - len(parts[1]) % 4)
        payload = base64.urlsafe_b64decode(padded).decode("utf-8")
        return json.loads(payload)
    except Exception:
        return None


def main():
    url = sys.argv[1] if len(sys.argv) > 1 else f"{BASE}/login"
    print(f"[GREEN-HAT] Session Dump v0.1")
    print(f"[GREEN-HAT] Alvo: {url}")
    print("---")

    cookies = dump_cookies(url)
    if not cookies:
        print("[GREEN-HAT] Nenhum cookie encontrado")
        return

    for name, info in cookies.items():
        print(f"[COOKIE] {name}")
        print(f"  valor:    {info['value'][:60]}{'...' if len(info['value']) > 60 else ''}")
        print(f"  domain:   {info['domain']}")
        print(f"  secure:   {info['secure']}")
        print(f"  httponly:  {info['httponly']}")

        # Tenta decodificar
        decoded = try_decode_base64(info["value"])
        if decoded:
            print(f"  base64:   {decoded[:80]}")

        jwt = try_decode_jwt(info["value"])
        if jwt:
            print(f"  JWT:      {json.dumps(jwt)}")
        print()

    print("---")
    print(
        f"[GREEN-HAT] {len(cookies)} cookies encontrados. "
        f"HttpOnly: {sum(1 for c in cookies.values() if c['httponly'])}/{len(cookies)}"
    )


if __name__ == "__main__":
    main()
# PULL REQUEST END
