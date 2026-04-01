# Roleplay: QA Tester — Edge cases & form validation via HTTP
# Foco: caracteres especiais, campos vazios, inputs longos, UX
# PULL REQUEST START
"""
QA Tester — testa como um usuário final persistente.
Inputs inesperados, edge cases, caracteres especiais, duplo submit.
"""
import os
import pytest
import requests

BASE = os.environ.get("APP_URL", "http://127.0.0.1:8000")

SPECIAL_NAMES = [
    "O'Brien",
    "名前テスト",
    "اسم",
    "José María Ñoño",
    "Teste 🎉👍",
    "<b>negrito</b>",
    "<script>alert(1)</script>",
    "test\\path\\file",
    "test\ttab\nnewline",
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


class TestQaSpecialCharacters:
    """Caracteres especiais em campos de busca e login."""

    @pytest.mark.parametrize("name", SPECIAL_NAMES)
    def test_busca_com_nome_especial(self, session, name):
        r = session.get(f"{BASE}/invoices", params={"search": name}, timeout=10)
        assert r.status_code != 500, f"[QA] 500 com: {name}"

    @pytest.mark.parametrize("name", SPECIAL_NAMES)
    def test_login_com_nome_especial(self, session, name):
        r = session.post(
            f"{BASE}/login",
            data={"email": name, "password": name, "_token": "fake"},
            timeout=10,
            allow_redirects=True,
        )
        assert r.status_code != 500, f"[QA] Login 500 com: {name}"


class TestQaEmptyFields:
    """Campos vazios e somente espaços."""

    def test_login_campos_vazios(self, session):
        r = session.post(
            f"{BASE}/login",
            data={"email": "", "password": "", "_token": "fake"},
            timeout=10,
            allow_redirects=True,
        )
        assert r.status_code != 500

    def test_login_somente_espacos(self, session):
        r = session.post(
            f"{BASE}/login",
            data={"email": "   ", "password": "   ", "_token": "fake"},
            timeout=10,
            allow_redirects=True,
        )
        assert r.status_code != 500


class TestQaLongInput:
    """Inputs muito longos."""

    def test_busca_com_5000_chars(self, session):
        r = session.get(
            f"{BASE}/invoices",
            params={"search": "a" * 5000},
            timeout=15,
        )
        assert r.status_code != 500, "[QA] Input de 5000 chars causou 500"

    def test_login_com_email_gigante(self, session):
        r = session.post(
            f"{BASE}/login",
            data={
                "email": "a" * 2000 + "@test.com",
                "password": "b" * 2000,
                "_token": "fake",
            },
            timeout=10,
            allow_redirects=True,
        )
        assert r.status_code != 500


class TestQaDuploSubmit:
    """Submissão repetida do mesmo formulário."""

    def test_5_submissoes_rapidas_login(self, session):
        for i in range(5):
            r = session.post(
                f"{BASE}/login",
                data={
                    "email": "nonexistent@test.com",
                    "password": "wrong",
                    "_token": "fake",
                },
                timeout=10,
                allow_redirects=True,
            )
            assert r.status_code != 500, f"[QA] Submit #{i} causou 500"


class TestQaUrlManipulation:
    """Manipulação de URL pelo usuário curioso."""

    def test_parametros_extras_ignorados(self, session):
        r = session.get(
            f"{BASE}/invoices",
            params={"search": "test", "admin": "1", "debug": "true"},
            timeout=10,
        )
        assert r.status_code != 500

    def test_path_traversal(self, session):
        r = session.get(f"{BASE}/invoices/../../../etc/passwd", timeout=10)
        assert r.status_code != 500
        assert "root:x" not in r.text


class TestQaFriendlyErrors:
    """Mensagens de erro amigáveis — sem stack trace."""

    def test_404_sem_stack_trace(self, session):
        r = session.get(f"{BASE}/pagina-qa-inexistente", timeout=10)
        assert "vendor/" not in r.text.lower()
        assert "sqlstate" not in r.text.lower()
# PULL REQUEST END
