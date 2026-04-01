# Roleplay: Green Hat (Script Kiddie) — Smoke test ingênuo via HTTP
# "Copiei esses payloads de um fórum hacker..."
# PULL REQUEST START
"""
Testes simples — um iniciante tentando payloads prontos.
Rápido, sem sofisticação. Só quer ver se "funciona".
"""
import os
import pytest
import requests

BASE = os.environ.get("APP_URL", "http://127.0.0.1:8000")

PAYLOADS = [
    "' OR '1'='1",
    "admin'--",
    "1 OR 1=1",
]


@pytest.fixture(scope="module")
def session():
    s = requests.Session()
    try:
        s.get(f"{BASE}/login", timeout=10)
    except requests.ConnectionError:
        pytest.skip("Servidor indisponível")
    yield s
    s.close()


@pytest.mark.parametrize("payload", PAYLOADS)
def test_login_rejeita_payload_simples(session, payload):
    """Tentar login com payload copiado da internet."""
    r = session.post(
        f"{BASE}/login",
        data={"email": payload, "password": payload, "_token": "fake"},
        timeout=10,
        allow_redirects=True,
    )
    assert r.status_code != 500, f"500 com payload: {payload}"


@pytest.mark.parametrize("payload", PAYLOADS)
def test_busca_nao_crashou(session, payload):
    """Colocar payload no campo de busca e ver o que acontece."""
    r = session.get(f"{BASE}/invoices", params={"search": payload}, timeout=10)
    assert r.status_code != 500


def test_aspas_na_url(session):
    """E se eu colocar aspas simples na URL?"""
    r = session.get(f"{BASE}/invoices/' OR '1'='1", timeout=10)
    assert r.status_code != 500
# PULL REQUEST END
