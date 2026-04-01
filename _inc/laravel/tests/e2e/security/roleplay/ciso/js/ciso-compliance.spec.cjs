// Roleplay: CISO — E2E compliance audit via Playwright
// Foco: headers, CSRF, exposição de dados, autenticação
// PULL REQUEST START
// @ts-check
const { test, expect } = require("@playwright/test");

const BASE = process.env.BASE_URL || "http://127.0.0.1:8000";

test.describe("CISO — E2E Compliance Audit", () => {
  test.describe("CSRF Protection", () => {
    test("POST /login sem CSRF token retorna 419", async ({ request }) => {
      const r = await request.post(`${BASE}/login`, {
        form: { email: "test@test.com", password: "test" },
      });
      // 419 (CSRF) ou redirect
      expect([302, 419, 405]).toContain(r.status());
    });

    test("formulário de login contém CSRF token", async ({ request }) => {
      const r = await request.get(`${BASE}/login`);
      const body = await r.text();
      const hasCsrf =
        body.includes("_token") || body.includes("csrf-token");
      expect(hasCsrf).toBe(true);
    });
  });

  test.describe("Security Headers", () => {
    test("X-Frame-Options ou CSP frame-ancestors presente", async ({
      request,
    }) => {
      const r = await request.get(`${BASE}/login`);
      const xfo = r.headers()["x-frame-options"];
      const csp = r.headers()["content-security-policy"] || "";
      expect(xfo !== undefined || csp.includes("frame-ancestors")).toBe(true);
    });
  });

  test.describe("Authentication enforcement", () => {
    const routes = ["/dashboard", "/invoices", "/customers"];

    for (const route of routes) {
      test(`${route} requer autenticação`, async ({ request }) => {
        const r = await request.get(`${BASE}${route}`, {
          maxRedirects: 0,
        });
        // Deve redirecionar (302) ou proibir (403)
        expect([301, 302, 303, 403]).toContain(r.status());
      });
    }
  });

  test.describe("Data Exposure", () => {
    test(".env não acessível via web", async ({ request }) => {
      const r = await request.get(`${BASE}/.env`);
      expect(r.status()).not.toBe(200);
    });

    test("phpinfo não acessível", async ({ request }) => {
      const r = await request.get(`${BASE}/phpinfo`);
      // 404 = rota não existe = phpinfo não exposto (DebugBar pode incluir PHP version no body)
      expect([403, 404]).toContain(r.status());
    });

    test("página de erro não expõe stack trace", async ({ request }) => {
      const r = await request.get(`${BASE}/rota-inexistente-ciso`);
      const body = await r.text();
      // Em produção não deve ter vendor/ paths
      expect(body).not.toMatch(/vendor\/.*\.php:\d+/);
    });
  });
});
// PULL REQUEST END
