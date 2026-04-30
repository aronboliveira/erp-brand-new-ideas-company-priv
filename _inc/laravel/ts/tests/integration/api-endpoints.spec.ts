/**
 * Integration tests: Mock API endpoint verification.
 *
 * These tests verify the mock API server returns correct data
 * for all major endpoint categories. Playwright hits the mock
 * server to validate the contract the route scripts depend on.
 *
 * Run:
 *   npx playwright test tests/integration/api-endpoints.spec.ts
 *
 * Requires mock-api-server running on port 3334.
 */

import { test, expect } from "@playwright/test";

const API = "http://localhost:3334";

/* ---------- Health ------------------------------------------------------ */

test.describe("Mock API — Health", () => {
  test("GET / returns status ok", async ({ request }) => {
    const res = await request.get(`${API}/`);
    expect(res.ok()).toBeTruthy();
    const json = await res.json();
    expect(json.status).toBe("ok");
    expect(json.server).toBe("mock-api");
  });

  test("GET /api/health returns uptime", async ({ request }) => {
    const res = await request.get(`${API}/api/health`);
    expect(res.ok()).toBeTruthy();
    const json = await res.json();
    expect(json.status).toBe("ok");
    expect(typeof json.uptime).toBe("number");
  });
});

/* ---------- Auth -------------------------------------------------------- */

test.describe("Mock API — Auth", () => {
  test("GET /api/user returns mock user", async ({ request }) => {
    const res = await request.get(`${API}/api/user`);
    expect(res.ok()).toBeTruthy();
    const json = await res.json();
    expect(json.data.id).toBe(1);
    expect(json.data.email).toBe("test@erp-prestech.local");
  });

  test("POST /api/login with valid credentials returns token", async ({ request }) => {
    const res = await request.post(`${API}/api/login`, {
      data: { email: "test@erp-prestech.local", password: "password" },
    });
    expect(res.ok()).toBeTruthy();
    const json = await res.json();
    expect(json.token).toBeTruthy();
    expect(json.user.role).toBe("admin");
  });

  test("POST /api/login with invalid credentials returns 401", async ({ request }) => {
    const res = await request.post(`${API}/api/login`, {
      data: { email: "wrong@test.local", password: "wrong" },
    });
    expect(res.status()).toBe(401);
    const json = await res.json();
    expect(json.error).toBe("Invalid credentials");
  });
});

/* ---------- Customers --------------------------------------------------- */

test.describe("Mock API — Customers", () => {
  test("GET /api/customers returns list", async ({ request }) => {
    const res = await request.get(`${API}/api/customers`);
    expect(res.ok()).toBeTruthy();
    const json = await res.json();
    expect(json.data.length).toBe(3);
    expect(json.total).toBe(3);
  });

  test("GET /api/customers/1 returns single customer", async ({ request }) => {
    const res = await request.get(`${API}/api/customers/1`);
    expect(res.ok()).toBeTruthy();
    const json = await res.json();
    expect(json.data.name).toBe("Acme Corp");
  });

  test("GET /api/customers/999 returns 404", async ({ request }) => {
    const res = await request.get(`${API}/api/customers/999`);
    expect(res.status()).toBe(404);
  });

  test("POST /api/customers creates new", async ({ request }) => {
    const res = await request.post(`${API}/api/customers`, {
      data: { name: "New Corp", email: "new@test.local" },
    });
    expect(res.status()).toBe(201);
    const json = await res.json();
    expect(json.data.id).toBe(99);
    expect(json.message).toBe("Created");
  });

  test("DELETE /api/customers/1 returns success", async ({ request }) => {
    const res = await request.delete(`${API}/api/customers/1`);
    expect(res.ok()).toBeTruthy();
    const json = await res.json();
    expect(json.message).toBe("Deleted");
  });
});

/* ---------- Invoices ---------------------------------------------------- */

test.describe("Mock API — Invoices", () => {
  test("GET /api/invoices returns list with totals", async ({ request }) => {
    const res = await request.get(`${API}/api/invoices`);
    expect(res.ok()).toBeTruthy();
    const json = await res.json();
    expect(json.data.length).toBe(3);
    expect(json.data[0].status).toBe("paid");
    expect(json.data[2].status).toBe("overdue");
  });

  test("GET /api/invoices/2 returns pending invoice", async ({ request }) => {
    const res = await request.get(`${API}/api/invoices/2`);
    expect(res.ok()).toBeTruthy();
    const json = await res.json();
    expect(json.data.total).toBe(3200.5);
    expect(json.data.status).toBe("pending");
  });
});

/* ---------- Products ---------------------------------------------------- */

test.describe("Mock API — Products", () => {
  test("GET /api/products returns list", async ({ request }) => {
    const res = await request.get(`${API}/api/products`);
    expect(res.ok()).toBeTruthy();
    const json = await res.json();
    expect(json.data.length).toBe(3);
    expect(json.data[0].sku).toBe("WDG-A");
  });
});

/* ---------- Translations ------------------------------------------------ */

test.describe("Mock API — Translations", () => {
  test("GET /api/translations returns all langs", async ({ request }) => {
    const res = await request.get(`${API}/api/translations`);
    expect(res.ok()).toBeTruthy();
    const json = await res.json();
    expect(json.data.en.greeting).toBe("Hello");
    expect(json.data.pt.greeting).toBe("Olá");
    expect(json.data.es.greeting).toBe("Hola");
  });

  test("GET /api/translations/pt returns Portuguese", async ({ request }) => {
    const res = await request.get(`${API}/api/translations/pt`);
    expect(res.ok()).toBeTruthy();
    const json = await res.json();
    expect(json.data.save).toBe("Salvar");
  });

  test("GET /api/translations/xx returns 404", async ({ request }) => {
    const res = await request.get(`${API}/api/translations/xx`);
    expect(res.status()).toBe(404);
  });
});

/* ---------- Dashboard --------------------------------------------------- */

test.describe("Mock API — Dashboard", () => {
  test("GET /api/dashboard returns aggregates", async ({ request }) => {
    const res = await request.get(`${API}/api/dashboard`);
    expect(res.ok()).toBeTruthy();
    const json = await res.json();
    expect(json.data.total_customers).toBe(3);
    expect(json.data.total_revenue).toBe(5450.75);
  });
});

/* ---------- Settings ---------------------------------------------------- */

test.describe("Mock API — Settings", () => {
  test("GET /api/settings returns config", async ({ request }) => {
    const res = await request.get(`${API}/api/settings`);
    expect(res.ok()).toBeTruthy();
    const json = await res.json();
    expect(json.data.currency).toBe("USD");
    expect(json.data.currency_symbol).toBe("$");
  });
});

/* ---------- CORS -------------------------------------------------------- */

test.describe("Mock API — CORS", () => {
  test("OPTIONS returns 204 with CORS headers", async ({ request }) => {
    const res = await request.fetch(`${API}/api/customers`, { method: "OPTIONS" });
    expect(res.status()).toBe(204);
    expect(res.headers()["access-control-allow-origin"]).toBe("*");
  });
});

/* ---------- 404 --------------------------------------------------------- */

test.describe("Mock API — 404", () => {
  test("Unmatched route returns 404 JSON", async ({ request }) => {
    const res = await request.get(`${API}/api/nonexistent`);
    expect(res.status()).toBe(404);
    const json = await res.json();
    expect(json.error).toBe("Not found");
  });
});
