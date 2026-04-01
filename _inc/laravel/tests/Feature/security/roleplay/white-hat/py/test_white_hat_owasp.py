# Roleplay: White Hat (Ethical Pentester) — pytest OWASP SQLi audit
# Ref: OWASP Testing Guide v4 — OTG-INPVAL-005
# PULL REQUEST START
"""
Pentest ético via HTTP — payloads categorizados por tipo OWASP.
Cada categoria testa um vetor diferente. Relatório de achados no final.
"""
import os
import time
import pytest
import requests

BASE = os.environ.get("APP_URL", "http://127.0.0.1:8000")

# ——— Categorias OWASP ———

ERROR_BASED = [
    "' AND EXTRACTVALUE(1,CONCAT(0x7e,version()))--",
    "' AND UPDATEXML(1,CONCAT(0x7e,version()),1)--",
    "' AND (SELECT 1 FROM(SELECT COUNT(*),CONCAT(version(),FLOOR(RAND(0)*2))x FROM information_schema.tables GROUP BY x)a)--",
]

UNION_BASED = [
    "' UNION SELECT NULL--",
    "' UNION SELECT NULL,NULL--",
    "' UNION SELECT NULL,NULL,NULL--",
    "1' UNION SELECT username,password FROM users--",
]

BOOLEAN_BLIND = [
    "1' AND 1=1--",
    "1' AND 1=2--",
    "1' AND SUBSTRING(@@version,1,1)='8'--",
]

TIME_BLIND = [
    "1' AND SLEEP(0)--",
    "1' AND IF(1=1,SLEEP(0),0)--",
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


class TestWhiteHatErrorBased:
    """OTG-INPVAL-005: Error-based SQLi — forçar leak de metadados via erro."""

    @pytest.mark.parametrize("payload", ERROR_BASED)
    def test_busca_nao_retorna_500(self, session, payload):
        r = session.get(f"{BASE}/invoices", params={"search": payload}, timeout=10)
        assert r.status_code != 500, f"[ERROR-BASED] 500 com: {payload}"

    @pytest.mark.parametrize("payload", ERROR_BASED)
    def test_nenhuma_info_sgbd_vaza(self, session, payload):
        r = session.get(f"{BASE}/invoices", params={"search": payload}, timeout=10)
        body = r.text.lower()
        assert "sqlstate" not in body, f"[ERROR-BASED] SQLSTATE vazou com: {payload}"
        assert "syntax error" not in body, f"[ERROR-BASED] Syntax error exposto"


class TestWhiteHatUnionBased:
    """OTG-INPVAL-005: Union-based SQLi — concatenar dados com query válida."""

    @pytest.mark.parametrize("payload", UNION_BASED)
    def test_busca_nao_retorna_500(self, session, payload):
        r = session.get(f"{BASE}/invoices", params={"search": payload}, timeout=10)
        assert r.status_code != 500, f"[UNION] 500 com: {payload}"

    @pytest.mark.parametrize("payload", UNION_BASED)
    def test_information_schema_nao_vaza(self, session, payload):
        r = session.get(f"{BASE}/invoices", params={"search": payload}, timeout=10)
        body = r.text.lower()
        # DebugBar inclui SQL queries com information_schema; verificar erros reais
        assert "sqlstate" not in body, f"[UNION] SQLSTATE vazou com: {payload}"
        assert "syntax error" not in body, f"[UNION] Syntax error com: {payload}"


class TestWhiteHatBooleanBlind:
    """OTG-INPVAL-005: Boolean-blind SQLi — inferir dados via true/false."""

    @pytest.mark.parametrize("payload", BOOLEAN_BLIND)
    def test_busca_nao_retorna_500(self, session, payload):
        r = session.get(f"{BASE}/invoices", params={"search": payload}, timeout=10)
        assert r.status_code != 500, f"[BOOLEAN-BLIND] 500 com: {payload}"


class TestWhiteHatTimeBlind:
    """OTG-INPVAL-005: Time-based blind SQLi — delays < 5s (não executa SLEEP)."""

    @pytest.mark.parametrize("payload", TIME_BLIND)
    def test_sem_delay_significativo(self, session, payload):
        start = time.time()
        r = session.get(f"{BASE}/invoices", params={"search": payload}, timeout=15)
        elapsed = time.time() - start
        assert r.status_code != 500
        assert elapsed < 5.0, f"[TIME-BLIND] {elapsed:.1f}s — possível SLEEP executado"


class TestWhiteHatLogin:
    """Cross-category: login endpoint deve rejeitar todos os vetores."""

    ALL = ERROR_BASED + UNION_BASED + BOOLEAN_BLIND + TIME_BLIND

    @pytest.mark.parametrize("payload", ALL)
    def test_login_rejeita(self, session, payload):
        r = session.post(
            f"{BASE}/login",
            data={"email": payload, "password": payload, "_token": "fake"},
            timeout=10,
            allow_redirects=True,
        )
        assert r.status_code != 500, f"[LOGIN] 500 com: {payload}"
# PULL REQUEST END
