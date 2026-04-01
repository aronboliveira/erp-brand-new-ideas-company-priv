# tests/python/security/test_sqli_payloads.py
# Auditoria de segurança — Testes de injeção SQL via HTTP (pytest)
# Simula ataques de SQL injection contra endpoints da aplicação
# Função: Pentester / Red Team — valida que o WAF e ORM
# neutralizam payloads maliciosos.
# PULL REQUEST START
"""
SQL Injection Penetration Tests — pytest + requests

Envia payloads SQLi via HTTP contra o servidor de desenvolvimento.
Verifica que nenhuma resposta contém:
  - Status 500 (erro de servidor)
  - Mensagens de erro SQL (SQLSTATE, syntax error, QueryException)
  - Vazamento de dados de banco (tabelas, colunas, versão)

Requer: pip install requests pytest
Servidor deve estar rodando em http://127.0.0.1:8000
"""

import os
import re
import time
from typing import Generator

import pytest
import requests

BASE_URL = os.environ.get("APP_URL", "http://127.0.0.1:8000")
SESSION = requests.Session()

# ════════════════════════════════════════════════════════════════════
#  Payloads
# ════════════════════════════════════════════════════════════════════

SQLI_PAYLOADS: list[str] = [
    "' OR '1'='1",
    "' OR '1'='1' --",
    "' OR '1'='1' /*",
    "'; DROP TABLE users; --",
    "1; DROP TABLE settings; --",
    "' UNION SELECT NULL,NULL,NULL --",
    "' UNION SELECT username,password,NULL FROM users --",
    "1' AND 1=1 --",
    "admin'--",
    "1' ORDER BY 100 --",
    "1' AND SLEEP(3) --",
    "1' AND BENCHMARK(5000000,SHA1('test'))--",
    "-1 OR 1=1",
    "' OR '' = '",
    "1%27%20OR%20%271%27%3D%271",
]

SEARCH_PAYLOADS: list[str] = [
    "%' OR 1=1 --",
    "%'; DROP TABLE invoices; --",
    "%%",
    "%_%",
    "test' AND '1'='1",
    "' HAVING 1=1 --",
]

TYPE_JUGGLING: list[str] = [
    "0", "null", "undefined", "NaN", "true", "false",
    "[]", "{}", "-1", "99999999999999999999", "0x1", "0e1",
]

# Padrões que indicam vazamento SQL na resposta
SQL_LEAK_PATTERNS = [
    re.compile(r"SQLSTATE", re.IGNORECASE),
    re.compile(r"syntax error", re.IGNORECASE),
    re.compile(r"QueryException", re.IGNORECASE),
    re.compile(r"Undefined table", re.IGNORECASE),
    re.compile(r"Base table or view not found", re.IGNORECASE),
    re.compile(r"mysql_", re.IGNORECASE),
    re.compile(r"PDOException", re.IGNORECASE),
    re.compile(r"pg_query", re.IGNORECASE),
    re.compile(r"ORA-\d{5}", re.IGNORECASE),
]

# ════════════════════════════════════════════════════════════════════
#  Fixtures
# ════════════════════════════════════════════════════════════════════

@pytest.fixture(scope="module")
def auth_session() -> Generator[requests.Session, None, None]:
    """Autentica uma sessão HTTP contra o servidor."""
    s = requests.Session()
    # Obter CSRF token da página de login
    try:
        login_page = s.get(f"{BASE_URL}/login", timeout=10)
        # Buscar CSRF token no HTML
        csrf_match = re.search(
            r'name="_token"\s+value="([^"]+)"', login_page.text
        )
        if csrf_match:
            token = csrf_match.group(1)
        else:
            token = ""

        # Tentar login com usuário de teste
        s.post(
            f"{BASE_URL}/login",
            data={
                "email": os.environ.get(
                    "TEST_USER_EMAIL",
                    "u_68ca0ef2-8cf2-4930-9129-24da61a4874a@test.local",
                ),
                "password": os.environ.get("TEST_USER_PASS", "123456"),
                "_token": token,
            },
            timeout=15,
            allow_redirects=True,
        )
    except requests.ConnectionError:
        pytest.skip("Servidor não disponível em " + BASE_URL)

    yield s
    s.close()


def _check_no_sql_leak(response: requests.Response, context: str) -> None:
    """Verifica que a resposta não contém vazamento de erro SQL."""
    text = response.text[:5000]  # Verificar só os primeiros 5KB
    for pattern in SQL_LEAK_PATTERNS:
        assert not pattern.search(text), (
            f"Vazamento SQL detectado ({pattern.pattern}) em {context} "
            f"— status {response.status_code}"
        )


# ════════════════════════════════════════════════════════════════════
#  Testes: Login sem autenticação
# ════════════════════════════════════════════════════════════════════

@pytest.mark.parametrize("payload", SQLI_PAYLOADS, ids=[f"sqli_{i}" for i in range(len(SQLI_PAYLOADS))])
def test_login_rejects_sqli(payload: str) -> None:
    """POST /login com payloads SQLi deve retornar ≠ 500."""
    s = requests.Session()
    try:
        login_page = s.get(f"{BASE_URL}/login", timeout=10)
    except requests.ConnectionError:
        pytest.skip("Servidor indisponível")

    csrf = re.search(r'name="_token"\s+value="([^"]+)"', login_page.text)
    token = csrf.group(1) if csrf else ""

    resp = s.post(
        f"{BASE_URL}/login",
        data={"email": payload, "password": payload, "_token": token},
        timeout=15,
        allow_redirects=True,
    )
    assert resp.status_code != 500, f"500 com payload: {payload}"
    _check_no_sql_leak(resp, f"POST /login email={payload}")


# ════════════════════════════════════════════════════════════════════
#  Testes: GET com query params SQLi (rotas autenticadas)
# ════════════════════════════════════════════════════════════════════

ROUTE_PARAMS = [
    ("/invoices", "search"),
    ("/invoices", "date"),
    ("/bills", "search"),
    ("/revenues", "date"),
    ("/revenues", "account"),
    ("/revenues", "customer"),
    ("/revenues", "category"),
    ("/payments", "search"),
    ("/employees", "branch"),
    ("/employees", "department"),
    ("/attendances", "month"),
    ("/attendances", "branch"),
    ("/attendances", "type"),
    ("/reports/leave", "month"),
    ("/reports/leave", "year"),
    ("/reports/leave", "branch"),
    ("/transactions", "start_month"),
    ("/transactions", "end_month"),
    ("/transactions", "account"),
    ("/customers", "search"),
    ("/home", "search"),
]


@pytest.mark.parametrize(
    "route,param",
    ROUTE_PARAMS,
    ids=[f"{r.replace('/','_')}_{p}" for r, p in ROUTE_PARAMS],
)
@pytest.mark.parametrize(
    "payload",
    SQLI_PAYLOADS[:5],
    ids=[f"sqli_{i}" for i in range(5)],
)
def test_get_routes_reject_sqli(
    auth_session: requests.Session, route: str, param: str, payload: str
) -> None:
    """GET rotas autenticadas com SQLi não devem retornar 500."""
    resp = auth_session.get(
        f"{BASE_URL}{route}", params={param: payload}, timeout=15
    )
    assert resp.status_code != 500, (
        f"500 em GET {route}?{param}={payload}"
    )
    _check_no_sql_leak(resp, f"GET {route}?{param}={payload}")


# ════════════════════════════════════════════════════════════════════
#  Testes: POST com payloads SQLi em formulários
# ════════════════════════════════════════════════════════════════════

POST_FORMS = [
    ("/customers", {"name": "sqli", "email": "sqli@test.local"}),
    ("/vendors", {"name": "sqli", "email": "sqli@test.local"}),
    ("/departments", {"name": "sqli"}),
    ("/pipelines", {"name": "sqli"}),
    ("/lead_stages", {"name": "sqli"}),
]


@pytest.mark.parametrize(
    "route,base_data",
    POST_FORMS,
    ids=[r.replace("/", "_").strip("_") for r, _ in POST_FORMS],
)
def test_post_forms_reject_sqli(
    auth_session: requests.Session, route: str, base_data: dict
) -> None:
    """POST formulários com SQLi devem retornar ≠ 500."""
    sqli = "' OR '1'='1' --"
    data = {k: sqli if v == "sqli" else v for k, v in base_data.items()}

    # Obter CSRF do formulário de criação
    create_page = auth_session.get(f"{BASE_URL}{route}/create", timeout=10)
    csrf = re.search(r'name="_token"\s+value="([^"]+)"', create_page.text)
    if csrf:
        data["_token"] = csrf.group(1)

    resp = auth_session.post(
        f"{BASE_URL}{route}", data=data, timeout=15, allow_redirects=True
    )
    assert resp.status_code != 500, f"500 em POST {route}"
    _check_no_sql_leak(resp, f"POST {route}")


# ════════════════════════════════════════════════════════════════════
#  Testes: Blind SQLi (time-based)
# ════════════════════════════════════════════════════════════════════

def test_time_based_blind_sqli_login() -> None:
    """SLEEP-based SQLi no login não deve causar delay > 4s."""
    s = requests.Session()
    try:
        login_page = s.get(f"{BASE_URL}/login", timeout=10)
    except requests.ConnectionError:
        pytest.skip("Servidor indisponível")

    csrf = re.search(r'name="_token"\s+value="([^"]+)"', login_page.text)
    token = csrf.group(1) if csrf else ""

    start = time.monotonic()
    resp = s.post(
        f"{BASE_URL}/login",
        data={
            "email": "' OR SLEEP(5) --",
            "password": "x",
            "_token": token,
        },
        timeout=15,
        allow_redirects=True,
    )
    elapsed = time.monotonic() - start

    assert resp.status_code != 500
    assert elapsed < 4.0, (
        f"Possível blind SQLi! Resposta demorou {elapsed:.1f}s (esperado < 4s)"
    )


@pytest.mark.parametrize(
    "route",
    ["/invoices", "/employees", "/customers"],
    ids=["invoices", "employees", "customers"],
)
def test_time_based_blind_sqli_search(
    auth_session: requests.Session, route: str
) -> None:
    """SLEEP-based SQLi em busca não deve causar delay > 4s."""
    start = time.monotonic()
    resp = auth_session.get(
        f"{BASE_URL}{route}",
        params={"search": "' OR SLEEP(5) --"},
        timeout=15,
    )
    elapsed = time.monotonic() - start

    assert resp.status_code != 500
    assert elapsed < 4.0, f"Possível blind SQLi em {route}! {elapsed:.1f}s"


# ════════════════════════════════════════════════════════════════════
#  Testes: Type Juggling
# ════════════════════════════════════════════════════════════════════

@pytest.mark.parametrize("value", TYPE_JUGGLING, ids=TYPE_JUGGLING)
def test_type_juggling_invoices(
    auth_session: requests.Session, value: str
) -> None:
    """Type juggling em /invoices?page= não deve causar 500."""
    resp = auth_session.get(
        f"{BASE_URL}/invoices", params={"page": value}, timeout=10
    )
    assert resp.status_code != 500, f"Type juggling 500: page={value}"


# ════════════════════════════════════════════════════════════════════
#  Testes: Path param injection
# ════════════════════════════════════════════════════════════════════

MALICIOUS_IDS = [
    "' OR '1'='1",
    "1 UNION SELECT NULL--",
    "99999999999999999999999",
    "../../../etc/passwd",
    "<script>alert(1)</script>",
    "'; DROP TABLE users; --",
]


@pytest.mark.parametrize(
    "route_tpl",
    ["/invoices/{}", "/employees/{}", "/customers/{}", "/bills/{}", "/deals/{}"],
    ids=["invoices", "employees", "customers", "bills", "deals"],
)
@pytest.mark.parametrize(
    "malicious_id",
    MALICIOUS_IDS,
    ids=[f"id_{i}" for i in range(len(MALICIOUS_IDS))],
)
def test_path_param_injection(
    auth_session: requests.Session, route_tpl: str, malicious_id: str
) -> None:
    """IDs maliciosos na URL devem retornar 404 ou 302, nunca 500."""
    url = f"{BASE_URL}{route_tpl.format(malicious_id)}"
    resp = auth_session.get(url, timeout=10, allow_redirects=False)
    assert resp.status_code != 500, f"500 em {url}"
    _check_no_sql_leak(resp, url)


# PULL REQUEST END
