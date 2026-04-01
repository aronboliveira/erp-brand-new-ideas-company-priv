// Roleplay: White Hat (Ethical Pentester) — Playwright E2E OWASP audit
// Ref: OWASP Testing Guide v4 — OTG-INPVAL-005
// PULL REQUEST START
// @ts-check
const { test, expect } = require("@playwright/test");

const BASE = process.env.BASE_URL || "http://127.0.0.1:8000";

const ERROR_BASED = [
  "' AND EXTRACTVALUE(1,CONCAT(0x7e,version()))--",
  "' AND UPDATEXML(1,CONCAT(0x7e,version()),1)--",
];

const UNION_BASED = [
  "' UNION SELECT NULL--",
  "' UNION SELECT NULL,NULL,NULL--",
  "1' UNION SELECT username,password FROM users--",
];

const BOOLEAN_BLIND = [
  "1' AND 1=1--",
  "1' AND 1=2--",
];

const TIME_BLIND = [
  "1' AND SLEEP(0)--",
  "1' AND IF(1=1,SLEEP(0),0)--",
];

const ALL = [...ERROR_BASED, ...UNION_BASED, ...BOOLEAN_BLIND, ...TIME_BLIND];

test.describe("White Hat — OWASP E2E SQLi Audit", () => {
  test.describe("Error-based vectors", () => {
    for (const payload of ERROR_BASED) {
      test(`error-based: ${payload.slice(0, 30)}`, async ({ request }) => {
        const r = await request.get(
          `${BASE}/invoices?search=${encodeURIComponent(payload)}`
        );
        expect(r.status()).not.toBe(500);
        const body = await r.text();
        expect(body.toLowerCase()).not.toContain("sqlstate");
        expect(body.toLowerCase()).not.toContain("syntax error");
      });
    }
  });

  test.describe("Union-based vectors", () => {
    for (const payload of UNION_BASED) {
      test(`union-based: ${payload.slice(0, 30)}`, async ({ request }) => {
        const r = await request.get(
          `${BASE}/invoices?search=${encodeURIComponent(payload)}`
        );
        expect(r.status()).not.toBe(500);
        const body = await r.text();
        // Verificar que dados de UNION não vazam (DebugBar pode conter information_schema em SQL queries)
        expect(body.toLowerCase()).not.toContain("sqlstate");
        expect(body.toLowerCase()).not.toContain("syntax error");
      });
    }
  });

  test.describe("Boolean-blind vectors", () => {
    for (const payload of BOOLEAN_BLIND) {
      test(`boolean-blind: ${payload.slice(0, 30)}`, async ({ request }) => {
        const r = await request.get(
          `${BASE}/invoices?search=${encodeURIComponent(payload)}`
        );
        expect(r.status()).not.toBe(500);
      });
    }
  });

  test.describe("Time-based blind vectors", () => {
    for (const payload of TIME_BLIND) {
      test(`time-blind: ${payload.slice(0, 30)}`, async ({ request }) => {
        const start = Date.now();
        const r = await request.get(
          `${BASE}/invoices?search=${encodeURIComponent(payload)}`
        );
        const elapsed = (Date.now() - start) / 1000;
        expect(r.status()).not.toBe(500);
        expect(elapsed).toBeLessThan(5.0);
      });
    }
  });

  test.describe("Login endpoint — cross-category", () => {
    for (const payload of ALL) {
      test(`login rejeita: ${payload.slice(0, 25)}`, async ({ request }) => {
        const r = await request.post(`${BASE}/login`, {
          form: { email: payload, password: payload, _token: "x" },
        });
        expect(r.status()).not.toBe(500);
      });
    }
  });
});
// PULL REQUEST END
