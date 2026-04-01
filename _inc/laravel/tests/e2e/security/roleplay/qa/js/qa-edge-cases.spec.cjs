// Roleplay: QA Tester — Playwright E2E edge cases & forms
// Foco: inputs inesperados, duplo submit, URL manipulation
// PULL REQUEST START
// @ts-nocheck
const { test, expect } = require("@playwright/test");

const BASE = process.env.BASE_URL || "http://127.0.0.1:8000";

const SPECIAL_NAMES = ["O'Brien", "名前テスト", "José María Ñoño", "Teste 🎉👍", "<script>alert(1)</script>", "<b>negrito</b>"];

test.describe("QA — E2E Edge Cases & Forms", () => {
  test.describe("Caracteres especiais na busca", () => {
    for (const name of SPECIAL_NAMES) {
      test(`busca com "${name.slice(0, 20)}" não crashou`, async ({ request }) => {
        const r = await request.get(`${BASE}/invoices?search=${encodeURIComponent(name)}`);
        expect(r.status()).not.toBe(500);
      });
    }
  });

  test.describe("Campos vazios e espaços", () => {
    test("login com campos vazios", async ({ request }) => {
      const r = await request.post(`${BASE}/login`, {
        form: { email: "", password: "", _token: "x" },
      });
      expect(r.status()).not.toBe(500);
    });

    test("login com somente espaços", async ({ request }) => {
      const r = await request.post(`${BASE}/login`, {
        form: { email: "   ", password: "   ", _token: "x" },
      });
      expect(r.status()).not.toBe(500);
    });
  });

  test.describe("Input longo", () => {
    test("busca com 5000 caracteres", async ({ request }) => {
      const long = "a".repeat(5000);
      const r = await request.get(`${BASE}/invoices?search=${encodeURIComponent(long)}`);
      expect(r.status()).not.toBe(500);
    });
  });

  test.describe("Duplo submit", () => {
    test("5 submissões rápidas de login", async ({ request }) => {
      for (let i = 0; i < 5; i++) {
        const r = await request.post(`${BASE}/login`, {
          form: {
            email: "nonexistent@test.com",
            password: "wrong",
            _token: "x",
          },
        });
        expect(r.status()).not.toBe(500);
      }
    });
  });

  test.describe("URL manipulation", () => {
    test("parâmetros extras ignorados", async ({ request }) => {
      const r = await request.get(`${BASE}/invoices?search=test&admin=1&debug=true`);
      expect(r.status()).not.toBe(500);
    });

    test("path traversal bloqueado", async ({ request }) => {
      const r = await request.get(`${BASE}/invoices/../../../etc/passwd`);
      expect(r.status()).not.toBe(500);
      const body = await r.text();
      expect(body).not.toContain("root:x");
    });
  });

  test.describe("Mensagens de erro amigáveis", () => {
    test("404 sem stack trace", async ({ request }) => {
      const r = await request.get(`${BASE}/pagina-qa-inexistente`);
      const body = await r.text();
      expect(body).not.toMatch(/vendor\/.*\.php:\d+/);
      expect(body.toLowerCase()).not.toContain("sqlstate");
    });
  });
});
// PULL REQUEST END
