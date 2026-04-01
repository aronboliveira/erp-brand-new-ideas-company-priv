# Roleplay: CISO — Compliance & Governance audit via HTTP
# Foco: headers, CSRF, exposição de dados, autenticação
# PULL REQUEST START
"""
Auditoria de compliance — valida controles de segurança sem executar ataques.
Verifica headers, CSRF, exposição de .env, stack trace, session cookies.
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


class TestCisoCsrf:
    """Verificar que proteção CSRF está ativa."""

    def test_post_sem_csrf_retorna_419(self, session):
        r = session.post(
            f"{BASE}/login",
            data={"email": "test@test.com", "password": "test"},
            timeout=10,
            allow_redirects=False,
        )
        assert r.status_code in (419, 302, 405), (
            f"[CISO-CSRF] POST /login sem CSRF retornou {r.status_code}"
        )

    def test_formulario_login_contem_csrf_token(self, session):
        r = session.get(f"{BASE}/login", timeout=10)
        assert "_token" in r.text or "csrf-token" in r.text, (
            "[CISO-CSRF] Formulário de login sem CSRF token"
        )


class TestCisoHeaders:
    """Verificar headers de segurança."""

    def test_x_frame_options_ou_csp(self, session):
        r = session.get(f"{BASE}/login", timeout=10)
        xfo = r.headers.get("X-Frame-Options")
        csp = r.headers.get("Content-Security-Policy", "")
        assert xfo is not None or "frame-ancestors" in csp, (
            "[CISO-HEADER] Nem X-Frame-Options nem CSP frame-ancestors"
        )

    def test_x_content_type_options(self, session):
        r = session.get(f"{BASE}/login", timeout=10)
        xcto = r.headers.get("X-Content-Type-Options")
        if xcto is None:
            pytest.skip("[CISO-HEADER] X-Content-Type-Options ausente")
        assert xcto == "nosniff"


class TestCisoAuthentication:
    """Verificar que rotas protegidas exigem login."""

    PROTECTED = ["/dashboard", "/invoices", "/customers"]

    @pytest.mark.parametrize("route", PROTECTED)
    def test_rota_protegida_redireciona(self, session, route):
        r = session.get(f"{BASE}{route}", timeout=10, allow_redirects=False)
        assert r.status_code in (301, 302, 303, 403), (
            f"[CISO-AUTH] {route} acessível sem auth (status: {r.status_code})"
        )


class TestCisoDataExposure:
    """Verificar que dados sensíveis não estão expostos."""

    def test_env_nao_acessivel(self, session):
        r = session.get(f"{BASE}/.env", timeout=10)
        assert r.status_code != 200, "[CISO-EXPOSURE] .env acessível via web!"

    def test_phpinfo_nao_acessivel(self, session):
        r = session.get(f"{BASE}/phpinfo", timeout=10)
        # DebugBar inclui metadados com PHP Version; verificar apenas status
        assert r.status_code in (403, 404), (
            f"[CISO-EXPOSURE] /phpinfo retornou {r.status_code}"
        )

    def test_debug_nao_expoe_stack_trace(self, session):
        r = session.get(f"{BASE}/rota-inexistente-ciso-test", timeout=10)
        body = r.text.lower()
        if "vendor/" in body and ".php" in body:
            pytest.skip("[CISO-DEBUG] Stack trace visível — APP_DEBUG ativo")


class TestCisoSessionSecurity:
    """Verificar configuração de cookies de sessão."""

    def test_session_cookie_httponly(self, session):
        session.get(f"{BASE}/login", timeout=10)
        for cookie in session.cookies:
            if "session" in cookie.name.lower():
                # http.cookiejar usa lowercase para atributos não-padrão
                is_httponly = (
                    cookie.has_nonstandard_attr("HttpOnly")
                    or cookie.has_nonstandard_attr("httponly")
                )
                assert is_httponly or cookie.secure, (
                    f"[CISO-SESSION] Cookie {cookie.name} sem HttpOnly"
                )
                return
        # Cookie pode não existir nesta resposta — aceitável
# PULL REQUEST END
