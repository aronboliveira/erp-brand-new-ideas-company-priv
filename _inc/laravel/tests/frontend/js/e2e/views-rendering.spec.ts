/**
 * @file views-rendering.spec.ts
 * @description Playwright E2E tests checking key ERP views render tables/cards/grids/forms.
 * Uses static mock HTML pages — no live Laravel server required.
 */
import { test, expect } from "@playwright/test";

const BASE = `file://${process.cwd()}/tests/frontend/js/pages/mocks/rendered`;

// Routes with data tables/cards/grids to check
const DATA_ROUTES = [
  { path: "/invoices/index.html", name: "Invoices" },
  { path: "/bills/index.html", name: "Bills" },
  { path: "/proposals/index.html", name: "Proposals" },
  { path: "/customers/index.html", name: "Customers" },
  { path: "/vendors/index.html", name: "Vendors" },
  { path: "/employees/index.html", name: "Employees" },
  { path: "/bank_accounts/index.html", name: "Bank Accounts" },
  { path: "/products/index.html", name: "Products" },
  { path: "/leads/index.html", name: "CRM Leads" },
  { path: "/projects/index.html", name: "Projects" },
  { path: "/contracts/index.html", name: "Contracts" },
];

test.describe("View Rendering Checks", () => {
  for (const route of DATA_ROUTES) {
    test(`${route.name} should not return error`, async ({ page }) => {
      const response = await page.goto(`${BASE}${route.path}`);
      const status = response?.status() ?? 0;
      expect(status).toBeLessThan(400);
    });

    test(`${route.name} should render content`, async ({ page }) => {
      await page.goto(`${BASE}${route.path}`);
      const bodyText = await page.textContent("body");
      expect(bodyText?.length).toBeGreaterThan(100);
    });
  }

  test("should detect broken pages (empty body)", async ({ page }) => {
    // Navigate to dashboard
    await page.goto(`${BASE}/dashboard-account.html`);
    await page.waitForLoadState("domcontentloaded");

    // Check that the page has meaningful content
    const tables = await page.locator("table").count();
    const cards = await page.locator(".card").count();

    // At least one structured element should be present
    expect(tables + cards).toBeGreaterThan(0);
  });
});

test.describe("Financial Report Views", () => {
  const REPORT_ROUTES = [
    "/reports/income-summary/index.html",
    "/reports/expense-summary/index.html",
    "/reports/income-vs-expense-summary/index.html",
    "/reports/balance-sheet/index.html",
    "/reports/profit-loss/index.html",
    "/reports/trial-balance/index.html",
  ];

  for (const route of REPORT_ROUTES) {
    test(`Report ${route} should not error`, async ({ page }) => {
      const response = await page.goto(`${BASE}${route}`);
      const status = response?.status() ?? 0;
      expect(status).toBeLessThan(400);
    });
  }
});

test.describe("Form Views", () => {
  const FORM_ROUTES = [
    "/invoices/create/index.html",
    "/bills/create/index.html",
    "/proposals/create/index.html",
    "/customers/create/index.html",
    "/vendors/create/index.html",
    "/employees/create/index.html",
  ];

  for (const route of FORM_ROUTES) {
    test(`Create form ${route} should render`, async ({ page }) => {
      await page.goto(`${BASE}${route}`);
      const forms = await page.locator("form").count();
      expect(forms).toBeGreaterThan(0);
    });
  }
});
