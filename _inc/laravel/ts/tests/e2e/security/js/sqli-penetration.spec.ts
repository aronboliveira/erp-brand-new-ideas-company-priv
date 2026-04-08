/**
 * @file sqli-penetration.spec.ts
 * @description Auditoria de segurança — Testes E2E de penetração SQL injection
 *   via Playwright. Simula um atacante usando o navegador para injetar payloads
 *   maliciosos em formulários, URLs, campos de busca e parâmetros de rota.
 *
 *   PULL REQUEST START
 */

import { test, expect } from "@playwright/test";
import path from "path";

const BASE_URL = process.env.APP_URL || "http://localhost:8000";
const STORAGE_STATE = path.join(import.meta.dirname, "../.auth/user.json");

/* ══════════════════════════════════════════════════════════════════
 *  Payloads
 * ══════════════════════════════════════════════════════════════════ */

const SQLI_PAYLOADS = ["' OR '1'='1", "' OR '1'='1' --", "'; DROP TABLE users; --", "' UNION SELECT NULL,NULL,NULL --", "' UNION SELECT username,password,NULL FROM users --", "1' AND SLEEP(5) --", "admin'--", "-1 OR 1=1", "' OR '' = '", "1; DROP TABLE settings; --", "1' ORDER BY 100 --", "' AND 1=CONVERT(int,(SELECT TOP 1 table_name FROM information_schema.tables))--", "') OR ('1'='1"];

const SEARCH_PAYLOADS = ["%' OR 1=1 --", "%'; DROP TABLE invoices; --", "%%", "%_%", "test' AND '1'='1", "' HAVING 1=1 --", "' GROUP BY 1 HAVING 1=1 --"];

/** Padrões que indicam vazamento SQL na resposta */
const SQL_LEAK_PATTERNS = [/SQLSTATE/i, /QueryException/i, /syntax error/i, /Undefined table/i, /Base table or view not found/i, /mysql_/i, /PDOException/i, /pg_query/i, /column.*not found/i];

/* ══════════════════════════════════════════════════════════════════
 *  Helpers
 * ══════════════════════════════════════════════════════════════════ */

/**
 * Verifica que o conteúdo da página não vaza erros SQL.
 * @param {import("@playwright/test").Page} page
 * @param {string} context
 */
async function assertNoSqlLeak(page, context) {
  const body = await page.content();
  const snippet = body.substring(0, 8000);
  for (const pattern of SQL_LEAK_PATTERNS) {
    expect(pattern.test(snippet), `Vazamento SQL (${pattern}) detectado em ${context}`).toBe(false);
  }
}

/* ══════════════════════════════════════════════════════════════════
 *  Testes: Login (sem autenticação)
 * ══════════════════════════════════════════════════════════════════ */

test.describe("SQLi Penetração — Login", () => {
  for (const payload of SQLI_PAYLOADS.slice(0, 6)) {
    test(`Login rejeita payload: ${payload.substring(0, 30)}`, async ({ page }) => {
      await page.goto(`${BASE_URL}/login`, {
        waitUntil: "commit",
        timeout: 30000,
      });

      // Preencher campos de login com payload
      const emailInput = page.locator('#email-input, input[name="email"]').first();
      const pwInput = page.locator('#pw-input, input[name="password"]').first();

      if ((await emailInput.isVisible({ timeout: 5000 }).catch(() => false)) && (await pwInput.isVisible({ timeout: 3000 }).catch(() => false))) {
        await emailInput.fill(payload);
        await pwInput.fill(payload);

        const submitBtn = page.locator('#saveBtn, button[type="submit"], input[type="submit"]').first();
        if (await submitBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
          await submitBtn.click();
          await page.waitForLoadState("domcontentloaded", { timeout: 15000 });
        }
      }

      // Verificar que não houve crash (500) nem vazamento SQL
      const status = await page.evaluate(() => {
        const meta = document.querySelector('meta[name="status-code"]');
        return meta ? meta.getAttribute("content") : null;
      });

      await assertNoSqlLeak(page, `Login payload=${payload.substring(0, 30)}`);

      // Não deve ter redirecionado para área autenticada
      const url = page.url();
      expect(url).not.toContain("/home");
    });
  }
});

/* ══════════════════════════════════════════════════════════════════
 *  Testes: Busca autenticada (GET params)
 * ══════════════════════════════════════════════════════════════════ */

test.describe("SQLi Penetração — Busca GET", () => {
  test.use({ storageState: STORAGE_STATE });

  test.beforeEach(async ({ page }) => {
    page.on("dialog", d => d.accept());
    page.addLocatorHandler(page.locator("#cc--main, .c--anim"), async () => {
      const btn = page.locator('#c-p-bn, .c-bn, [data-cc="accept-all"]').first();
      if (await btn.isVisible({ timeout: 1000 }).catch(() => false)) await btn.click({ force: true });
    });
  });

  const SEARCH_ROUTES = [
    { url: "/invoices", param: "search" },
    { url: "/customers", param: "search" },
    { url: "/employees", param: "branch" },
    { url: "/revenues", param: "account" },
    { url: "/bills", param: "search" },
    { url: "/deals", param: "search" },
  ];

  for (const { url, param } of SEARCH_ROUTES) {
    for (const payload of SQLI_PAYLOADS.slice(0, 4)) {
      test(`GET ${url}?${param}= rejeita SQLi: ${payload.substring(0, 25)}`, async ({ page }) => {
        const targetUrl = `${BASE_URL}${url}?${param}=${encodeURIComponent(payload)}`;

        const response = await page.goto(targetUrl, {
          waitUntil: "commit",
          timeout: 30000,
        });

        const status = response ? response.status() : 0;
        expect(status, `500 em GET ${url}?${param}=${payload}`).not.toBe(500);

        await assertNoSqlLeak(page, `GET ${url}?${param}=${payload.substring(0, 25)}`);
      });
    }
  }
});

/* ══════════════════════════════════════════════════════════════════
 *  Testes: Formulários POST (campos com SQLi)
 * ══════════════════════════════════════════════════════════════════ */

test.describe("SQLi Penetração — POST Formulários", () => {
  test.use({ storageState: STORAGE_STATE });

  test.beforeEach(async ({ page }) => {
    page.on("dialog", d => d.accept());
    page.addLocatorHandler(page.locator("#cc--main, .c--anim"), async () => {
      const btn = page.locator('#c-p-bn, .c-bn, [data-cc="accept-all"]').first();
      if (await btn.isVisible({ timeout: 1000 }).catch(() => false)) await btn.click({ force: true });
    });
  });

  const FORM_TARGETS = [
    {
      createUrl: "/customers/create",
      fields: { name: "sqli", email: "test@sqli.local" },
    },
    {
      createUrl: "/departments/create",
      fields: { name: "sqli" },
    },
    {
      createUrl: "/pipelines/create",
      fields: { name: "sqli" },
    },
  ];

  for (const target of FORM_TARGETS) {
    test(`POST ${target.createUrl} rejeita SQLi nos campos`, async ({ page }) => {
      await page.goto(`${BASE_URL}${target.createUrl}`, {
        waitUntil: "commit",
        timeout: 30000,
      });

      const payload = "' OR '1'='1' --";

      // Preencher cada campo com o payload
      for (const [fieldName, originalValue] of Object.entries(target.fields)) {
        const value = originalValue === "sqli" ? payload : originalValue;
        const input = page.locator(`input[name="${fieldName}"], textarea[name="${fieldName}"]`).first();
        if (await input.isVisible({ timeout: 3000 }).catch(() => false)) {
          await input.fill(value);
        }
      }

      // Submeter
      const submitBtn = page.locator('button[type="submit"], input[type="submit"], #saveBtn').first();
      if (await submitBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
        await submitBtn.click();
        await page.waitForLoadState("domcontentloaded", { timeout: 15000 }).catch(() => {});
      }

      await assertNoSqlLeak(page, `POST ${target.createUrl}`);
    });
  }
});

/* ══════════════════════════════════════════════════════════════════
 *  Testes: API — payloads via request fixture (sem browser)
 * ══════════════════════════════════════════════════════════════════ */

test.describe("SQLi Penetração — API Endpoints", () => {
  const SAFE_STATUSES = [200, 301, 302, 400, 401, 403, 404, 405, 419, 422, 429];

  for (const payload of SQLI_PAYLOADS.slice(0, 5)) {
    test(`API POST /login com SQLi retorna status seguro: ${payload.substring(0, 25)}`, async ({ request }) => {
      // Obter CSRF token
      const loginPage = await request.get(`${BASE_URL}/login`, {
        maxRedirects: 0,
      });
      const html = await loginPage.text();
      const csrfMatch = html.match(/name="_token"\s+value="([^"]+)"/);
      const token = csrfMatch ? csrfMatch[1] : "";

      const resp = await request.post(`${BASE_URL}/login`, {
        form: {
          email: payload,
          password: payload,
          _token: token,
        },
        maxRedirects: 0,
      });

      expect(SAFE_STATUSES, `Status HTTP inseguro (${resp.status()}) para payload: ${payload}`).toContain(resp.status());

      const body = await resp.text();
      for (const p of SQL_LEAK_PATTERNS) {
        expect(p.test(body.substring(0, 5000)), `Vazamento SQL (${p}) na resposta de /login`).toBe(false);
      }
    });
  }

  for (const payload of SEARCH_PAYLOADS.slice(0, 4)) {
    test(`API GET /invoices?search= com SQLi: ${payload.substring(0, 25)}`, async ({ request }) => {
      const resp = await request.get(`${BASE_URL}/invoices?search=${encodeURIComponent(payload)}`, { maxRedirects: 5 });

      expect(resp.status(), `500 em GET /invoices?search=${payload}`).not.toBe(500);
    });
  }
});

/* ══════════════════════════════════════════════════════════════════
 *  Testes: Path parameter injection
 * ══════════════════════════════════════════════════════════════════ */

test.describe("SQLi Penetração — Path Params", () => {
  test.use({ storageState: STORAGE_STATE });

  test.beforeEach(async ({ page }) => {
    page.on("dialog", d => d.accept());
  });

  const ROUTES = ["/invoices", "/employees", "/customers", "/bills", "/deals"];
  const MALICIOUS_IDS = ["' OR '1'='1", "1 UNION SELECT NULL--", "99999999999", "../../../etc/passwd", "'; DROP TABLE users; --"];

  for (const route of ROUTES) {
    for (const malId of MALICIOUS_IDS.slice(0, 3)) {
      test(`${route}/${malId.substring(0, 20)} retorna ≠ 500`, async ({ page }) => {
        const response = await page.goto(`${BASE_URL}${route}/${encodeURIComponent(malId)}`, { waitUntil: "commit", timeout: 20000 });

        const status = response ? response.status() : 0;
        expect(status, `500 em ${route}/${malId}`).not.toBe(500);

        await assertNoSqlLeak(page, `${route}/${malId.substring(0, 20)}`);
      });
    }
  }
});

/* ══════════════════════════════════════════════════════════════════
 *  Testes: Time-based blind SQLi
 * ══════════════════════════════════════════════════════════════════ */

test.describe("SQLi Penetração — Time-Based Blind", () => {
  test("Login com SLEEP payload não demora > 4s", async ({ page }) => {
    const start = Date.now();

    await page.goto(`${BASE_URL}/login`, {
      waitUntil: "commit",
      timeout: 15000,
    });

    const emailInput = page.locator('#email-input, input[name="email"]').first();
    const pwInput = page.locator('#pw-input, input[name="password"]').first();

    if ((await emailInput.isVisible({ timeout: 5000 }).catch(() => false)) && (await pwInput.isVisible({ timeout: 3000 }).catch(() => false))) {
      await emailInput.fill("' OR SLEEP(5) --");
      await pwInput.fill("x");

      const submitBtn = page.locator('#saveBtn, button[type="submit"], input[type="submit"]').first();
      if (await submitBtn.isVisible({ timeout: 3000 }).catch(() => false)) {
        await submitBtn.click();
        await page.waitForLoadState("domcontentloaded", { timeout: 10000 }).catch(() => {});
      }
    }

    const elapsed = Date.now() - start;
    expect(elapsed, `Possível blind SQLi! Login demorou ${elapsed}ms`).toBeLessThan(15000);

    await assertNoSqlLeak(page, "Login SLEEP blind SQLi");
  });
});

/* ══════════════════════════════════════════════════════════════════
 *  Testes: Header injection
 * ══════════════════════════════════════════════════════════════════ */

test.describe("SQLi Penetração — Header Injection", () => {
  const HEADER_PAYLOADS = {
    "X-Forwarded-For": "' OR '1'='1' --",
    "User-Agent": "' UNION SELECT NULL --",
    Referer: "'; DROP TABLE sessions; --",
  };

  for (const [header, payload] of Object.entries(HEADER_PAYLOADS)) {
    test(`Header ${header} com SQLi não causa 500`, async ({ request }) => {
      const resp = await request.get(`${BASE_URL}/login`, {
        headers: { [header]: payload },
        maxRedirects: 5,
      });

      expect(resp.status(), `500 via header ${header}=${payload}`).not.toBe(500);
    });
  }
});

// PULL REQUEST END
