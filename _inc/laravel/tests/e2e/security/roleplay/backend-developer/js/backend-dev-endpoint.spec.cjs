// Roleplay: Backend Developer — Playwright E2E endpoint review
// Foco: autenticação, CSRF, content-type, middleware
// PULL REQUEST START
// @ts-check
const { test, expect } = require("@playwright/test");

const BASE = process.env.BASE_URL || "http://127.0.0.1:8000";

test.describe("Backend Developer — E2E Endpoint Review", () => {
  test.describe("Autenticação obrigatória em rotas protegidas", () => {
    const routes = ["/dashboard", "/invoices", "/customers", "/users"];
    for (const route of routes) {
      test(`GET ${route} redireciona sem auth`, async ({ request }) => {
        const r = await request.get(`${BASE}${route}`, {
          maxRedirects: 0,
        });
        expect([301, 302, 303, 403]).toContain(r.status());
      });
    }

    const postRoutes = ["/users", "/invoices", "/customers"];
    for (const route of postRoutes) {
      test(`POST ${route} sem auth retorna redirect/419`, async ({
        request,
      }) => {
        const r = await request.post(`${BASE}${route}`, {
          form: { name: "test" },
        });
        expect([301, 302, 401, 403, 419]).toContain(r.status());
      });
    }
  });

  test.describe("CSRF enforcement", () => {
    test("POST /login sem CSRF retorna 419", async ({ request }) => {
      const r = await request.post(`${BASE}/login`, {
        form: { email: "test@test.com", password: "test" },
      });
      expect([302, 419]).toContain(r.status());
    });
  });

  test.describe("SQLi via search params (code review regression)", () => {
    const payloads = [
      "' OR '1'='1",
      "1' UNION SELECT NULL--",
      "1'; DROP TABLE users; --",
    ];

    for (const p of payloads) {
      test(`search: ${p.slice(0, 25)}`, async ({ request }) => {
        const r = await request.get(
          `${BASE}/invoices?search=${encodeURIComponent(p)}`
        );
        expect(r.status()).not.toBe(500);
        const body = await r.text();
        expect(body.toLowerCase()).not.toContain("sqlstate");
      });
    }
  });

  test.describe("HTTP method enforcement", () => {
    test("GET /logout redireciona ou retorna 405", async ({ request }) => {
      const r = await request.get(`${BASE}/logout`, {
        maxRedirects: 0,
      });
      expect([302, 405]).toContain(r.status());
    });
  });

  test.describe("Headers de resposta", () => {
    test("Content-Type está definido", async ({ request }) => {
      const r = await request.get(`${BASE}/login`);
      const ct = r.headers()["content-type"];
      expect(ct).toBeDefined();
      expect(ct).toContain("text/html");
    });
  });
});
// PULL REQUEST END
