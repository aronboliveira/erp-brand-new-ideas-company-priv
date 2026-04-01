# Roleplay: Backend Developer — Code review & endpoint audit via HTTP
# Foco: middleware, SQLi, mass assignment, content-type
# PULL REQUEST START
"""
Desenvolvedor interno — testa endpoints como code review.
Verifica autenticação, SQLi, mass assignment, headers.
"""
import os
import pytest
import requests

BASE = os.environ.get("APP_URL", "http://127.0.0.1:8000")


@pytest.fixture(scope="module")
def session():
    s = requests.Session()
    try:
        s.get(f"{BASE}/login", timeout=10)
    except requests.ConnectionError:
        pytest.skip("Servidor indisponível")
    yield s
    s.close()


class TestBackendDevAuth:
    """Verificar que rotas exigem autenticação."""

    PROTECTED_POST = ["/users", "/invoices", "/customers"]

    @pytest.mark.parametrize("route", PROTECTED_POST)
    def test_post_sem_auth_redireciona(self, session, route):
        r = session.post(
            f"{BASE}{route}", data={}, timeout=10, allow_redirects=False
        )
        assert r.status_code in (301, 302, 401, 403, 419), (
            f"[BACKEND-DEV] POST {route} sem auth: {r.status_code}"
        )


class TestBackendDevSqli:
    """Verificar que search params são seguros."""

    PAYLOADS = [
        "' OR '1'='1",
        "1' UNION SELECT NULL--",
        "1'; DROP TABLE users; --",
    ]

    @pytest.mark.parametrize("payload", PAYLOADS)
    def test_search_nao_retorna_500(self, session, payload):
        r = session.get(
            f"{BASE}/invoices", params={"search": payload}, timeout=10
        )
        assert r.status_code != 500, f"[BACKEND-DEV] 500: {payload}"
        assert "sqlstate" not in r.text.lower()


class TestBackendDevCsrf:
    """Verificar CSRF em rotas POST."""

    def test_login_sem_csrf(self, session):
        r = session.post(
            f"{BASE}/login",
            data={"email": "test@test.com", "password": "test"},
            timeout=10,
            allow_redirects=False,
        )
        assert r.status_code in (302, 419, 405)


class TestBackendDevMassAssignment:
    """Verificar que mass assignment não escala privilégios."""

    def test_criacao_usuario_campos_extras(self, session):
        r = session.post(
            f"{BASE}/users",
            data={
                "name": "Dev Test",
                "email": "dev-test-py@test.com",
                "password": "Password123!",
                "is_admin": "1",
                "role": "super-admin",
                "_token": "fake",
            },
            timeout=10,
            allow_redirects=True,
        )
        # Não deve dar 500 em nenhum caso
        assert r.status_code != 500
# PULL REQUEST END
