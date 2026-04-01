// Roleplay: Green Hat (Script Kiddie) — E2E smoke com Playwright
// "Vou colar o payload no campo e ver se funciona..."
// PULL REQUEST START
// @ts-nocheck
const { test, expect } = require("@playwright/test");

const BASE = process.env.BASE_URL || "http://127.0.0.1:8000";

const PAYLOADS = ["' OR '1'='1", "admin'--", "1 OR 1=1"];

test.describe("Green Hat — Smoke E2E SQLi", () => {
  test("login com payload OR 1=1 não dá 500", async ({ request }) => {
    // "Copiei do StackOverflow"
    const r = await request.post(`${BASE}/login`, {
      form: { email: "' OR '1'='1", password: "' OR '1'='1", _token: "x" },
    });
    expect(r.status()).not.toBe(500);
  });

  for (const payload of PAYLOADS) {
    test(`busca com "${payload}" não explode`, async ({ request }) => {
      const r = await request.get(`${BASE}/invoices?search=${encodeURIComponent(payload)}`);
      // "Se não der 500, tá bom pra mim"
      expect(r.status()).not.toBe(500);
    });
  }

  test("aspas simples na URL não quebra", async ({ request }) => {
    const r = await request.get(`${BASE}/invoices/'`);
    expect(r.status()).not.toBe(500);
  });
});
// PULL REQUEST END
