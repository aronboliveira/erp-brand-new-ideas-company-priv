#!/usr/bin/env node
"use strict";

/**
 * mock-api-server.cjs — Lightweight mock API server for integration tests.
 *
 * Simulates the Laravel backend API endpoints used by route JS files.
 * Returns deterministic JSON responses so Playwright/Jest can assert
 * against them without needing a running PHP server.
 *
 * Usage:
 *   node tests/integration/mock-api-server.cjs [port]
 *   Default port: 3334
 *
 * Endpoints mimic the Laravel API patterns:
 *   GET    /api/*          → JSON responses
 *   POST   /api/*          → JSON with CSRF validation
 *   DELETE /api/*          → JSON success/error
 *   GET    /               → health check
 *
 * @see _inc/laravel/.notes/.llms/.guidelines/frontend/template-literal-testing.md
 */

const http = require("http");
const { URL } = require("url");

const PORT = parseInt(process.argv[2] || "3334", 10);

/* ---------- Mock Data --------------------------------------------------- */

const MOCK_USER = {
  id: 1,
  name: "Test User",
  email: "test@erp-brand-new-ideas-company.local",
  role: "admin",
};

const MOCK_CUSTOMERS = [
  { id: 1, name: "Acme Corp", email: "acme@test.local", phone: "555-0001" },
  { id: 2, name: "Globex Inc", email: "globex@test.local", phone: "555-0002" },
  { id: 3, name: "Initech", email: "initech@test.local", phone: "555-0003" },
];

const MOCK_INVOICES = [
  { id: 1, customer_id: 1, total: 1500.0, status: "paid", due_date: "2026-04-01" },
  { id: 2, customer_id: 2, total: 3200.5, status: "pending", due_date: "2026-04-15" },
  { id: 3, customer_id: 1, total: 750.25, status: "overdue", due_date: "2026-03-01" },
];

const MOCK_PRODUCTS = [
  { id: 1, name: "Widget A", price: 25.0, stock: 100, sku: "WDG-A" },
  { id: 2, name: "Widget B", price: 50.0, stock: 50, sku: "WDG-B" },
  { id: 3, name: "Gadget X", price: 150.0, stock: 20, sku: "GDG-X" },
];

const MOCK_EMPLOYEES = [
  { id: 1, name: "John Doe", department: "Engineering", salary: 75000 },
  { id: 2, name: "Jane Smith", department: "Sales", salary: 65000 },
];

const MOCK_TRANSLATIONS = {
  en: { greeting: "Hello", total: "Total", save: "Save", cancel: "Cancel", delete: "Delete" },
  pt: { greeting: "Olá", total: "Total", save: "Salvar", cancel: "Cancelar", delete: "Excluir" },
  es: { greeting: "Hola", total: "Total", save: "Guardar", cancel: "Cancelar", delete: "Eliminar" },
};

/* ---------- Route Table ------------------------------------------------- */

/**
 * @typedef {{ status?: number, body: unknown, headers?: Record<string, string> }} MockResponse
 */

/**
 * Route handler map: METHOD:path → handler(url, body)
 * @type {Record<string, (url: URL, body?: unknown) => MockResponse>}
 */
const ROUTES = {
  // Health check
  "GET:/": () => ({ body: { status: "ok", server: "mock-api", port: PORT } }),
  "GET:/api/health": () => ({ body: { status: "ok", uptime: process.uptime() } }),

  // Auth
  "GET:/api/user": () => ({ body: { data: MOCK_USER } }),
  "POST:/api/login": (_url, body) => {
    const b = /** @type {Record<string, unknown>} */ (body || {});
    if (b.email === "test@erp-brand-new-ideas-company.local" && b.password === "password") {
      return { body: { token: "mock-jwt-token-12345", user: MOCK_USER } };
    }
    return { status: 401, body: { error: "Invalid credentials" } };
  },
  "POST:/api/logout": () => ({ body: { message: "Logged out" } }),

  // Customers
  "GET:/api/customers": () => ({ body: { data: MOCK_CUSTOMERS, total: MOCK_CUSTOMERS.length } }),
  "GET:/api/customers/:id": url => {
    const id = parseInt(url.pathname.split("/").pop() || "0", 10);
    const c = MOCK_CUSTOMERS.find(c => c.id === id);
    return c ? { body: { data: c } } : { status: 404, body: { error: "Not found" } };
  },
  "POST:/api/customers": (_url, body) => ({
    body: { data: { id: 99, .../** @type {object} */ (body) }, message: "Created" },
    status: 201,
  }),
  "DELETE:/api/customers/:id": () => ({ body: { message: "Deleted" } }),

  // Invoices
  "GET:/api/invoices": () => ({ body: { data: MOCK_INVOICES, total: MOCK_INVOICES.length } }),
  "GET:/api/invoices/:id": url => {
    const id = parseInt(url.pathname.split("/").pop() || "0", 10);
    const inv = MOCK_INVOICES.find(i => i.id === id);
    return inv ? { body: { data: inv } } : { status: 404, body: { error: "Not found" } };
  },

  // Products
  "GET:/api/products": () => ({ body: { data: MOCK_PRODUCTS, total: MOCK_PRODUCTS.length } }),
  "GET:/api/products/:id": url => {
    const id = parseInt(url.pathname.split("/").pop() || "0", 10);
    const p = MOCK_PRODUCTS.find(p => p.id === id);
    return p ? { body: { data: p } } : { status: 404, body: { error: "Not found" } };
  },

  // Employees
  "GET:/api/employees": () => ({ body: { data: MOCK_EMPLOYEES, total: MOCK_EMPLOYEES.length } }),

  // Translations
  "GET:/api/translations": () => ({ body: { data: MOCK_TRANSLATIONS } }),
  "GET:/api/translations/:lang": url => {
    const lang = url.pathname.split("/").pop() || "en";
    const t = MOCK_TRANSLATIONS[lang];
    return t ? { body: { data: t } } : { status: 404, body: { error: `Lang '${lang}' not found` } };
  },

  // Generic CRUD success fallback
  "POST:/api/:resource": () => ({ status: 201, body: { message: "Created", id: 99 } }),
  "PUT:/api/:resource/:id": () => ({ body: { message: "Updated" } }),
  "PATCH:/api/:resource/:id": () => ({ body: { message: "Updated" } }),
  "DELETE:/api/:resource/:id": () => ({ body: { message: "Deleted" } }),

  // Dashboard
  "GET:/api/dashboard": () => ({
    body: {
      data: {
        total_customers: 3,
        total_invoices: 3,
        total_revenue: 5450.75,
        pending_invoices: 1,
        overdue_invoices: 1,
      },
    },
  }),

  // Settings
  "GET:/api/settings": () => ({
    body: {
      data: {
        company_name: "Test Company",
        currency: "USD",
        currency_symbol: "$",
        date_format: "Y-m-d",
        timezone: "UTC",
      },
    },
  }),
};

/* ---------- Request matching --------------------------------------------- */

/**
 * Match a request to a route handler.
 * Supports :param placeholders.
 *
 * @param {string} method
 * @param {string} pathname
 * @returns {((url: URL, body?: unknown) => MockResponse) | null}
 */
function matchRoute(method, pathname) {
  // Try exact match first
  const exact = `${method}:${pathname}`;
  if (ROUTES[exact]) return ROUTES[exact];

  // Try parameterised routes (split only on FIRST colon to preserve :param)
  for (const [pattern, handler] of Object.entries(ROUTES)) {
    const colonIdx = pattern.indexOf(":");
    const pMethod = pattern.slice(0, colonIdx);
    const pPath = pattern.slice(colonIdx + 1);
    if (pMethod !== method) continue;

    const pParts = pPath.split("/");
    const rParts = pathname.split("/");
    if (pParts.length !== rParts.length) continue;

    let match = true;
    for (let i = 0; i < pParts.length; i++) {
      if (pParts[i].startsWith(":")) continue; // wildcard
      if (pParts[i] !== rParts[i]) {
        match = false;
        break;
      }
    }
    if (match) return handler;
  }

  return null;
}

/* ---------- Server ------------------------------------------------------ */

const server = http.createServer((req, res) => {
  const url = new URL(req.url || "/", `http://localhost:${PORT}`);
  const method = (req.method || "GET").toUpperCase();

  // CORS headers for browser-based tests
  res.setHeader("Access-Control-Allow-Origin", "*");
  res.setHeader("Access-Control-Allow-Methods", "GET, POST, PUT, PATCH, DELETE, OPTIONS");
  res.setHeader("Access-Control-Allow-Headers", "Content-Type, X-CSRF-TOKEN, Accept, Authorization");

  if (method === "OPTIONS") {
    res.writeHead(204);
    res.end();
    return;
  }

  // Collect body
  let body = "";
  req.on("data", chunk => (body += chunk));
  req.on("end", () => {
    let parsedBody;
    try {
      parsedBody = body ? JSON.parse(body) : undefined;
    } catch {
      parsedBody = body;
    }

    const handler = matchRoute(method, url.pathname);

    if (!handler) {
      res.writeHead(404, { "Content-Type": "application/json" });
      res.end(JSON.stringify({ error: "Not found", path: url.pathname, method }));
      return;
    }

    const result = handler(url, parsedBody);
    const status = result.status || 200;
    const headers = {
      "Content-Type": "application/json; charset=utf-8",
      ...(result.headers || {}),
    };

    res.writeHead(status, headers);
    res.end(JSON.stringify(result.body));
  });
});

server.listen(PORT, () => {
  console.log(`Mock API server running at http://localhost:${PORT}`);
  console.log(`Endpoints: ${Object.keys(ROUTES).length} routes registered`);
  console.log("Press Ctrl+C to stop.");
});

// Graceful shutdown
process.on("SIGINT", () => {
  console.log("\nShutting down mock server...");
  server.close(() => process.exit(0));
});
process.on("SIGTERM", () => {
  server.close(() => process.exit(0));
});
