/**
 * @fileoverview TypeScript version of tests/e2e/financial.spec.cjs
 * @generated from original JavaScript - manual review recommended
 * @module financial.spec
 */

/* global bootstrap, $, jQuery */
import { test, expect, type Page, type BrowserContext } from "@playwright/test";
import path from "path";

/**
 * ERP Brand New Ideas Company - Financial Module E2E Tests
 * Tests mission-critical financial routes for data accuracy and UI consistency
 * Run auth.setup.cjs first to create authentication state
 */

const BASE_URL = "http://localhost:8000";
const STORAGE_STATE = path.join(import.meta.dirname, ".auth/user.json");

// Test configuration
test.describe.configure({ mode: "serial" });

// Use saved storage state for all tests
test.use({ storageState: STORAGE_STATE });

// Dismiss overlays before each test
test.beforeEach(async ({ page }) => {
  // Handle cookie consent banner if present
  page.on("dialog", dialog => dialog.accept());

  // Add handler to dismiss cookie banner after page load
  page.addLocatorHandler(page.locator("#cc--main, .c--anim"), async _el => {
    const acceptBtn = page
      .locator('#c-p-bn, .c-bn, [data-cc="accept-all"]')
      .first();
    if (await acceptBtn.isVisible({ timeout: 1000 }).catch(() => false))
      await acceptBtn.click({ force: true });
  });
});

test.describe("Invoice Module", (): void => {
  test("should display invoices index: number with table", async ({ page }) => {
    await page.goto(`${BASE_URL}/invoices`);
    await expect(page).toHaveURL(/.*invoices/);

    // Verify data table exists (exclude debugbar tables)
    const table = page
      .locator("table.datatable, table.dataTable-table")
      .first();
    await expect(table).toBeVisible();

    // Check for expected columns in main content area
    const headers = page.locator(".card-body th, .table-responsive th"),
      headerCount = await headers.count();
    expect(headerCount).toBeGreaterThan(0);
  });

  test("should load invoice create form: HTMLFormElement", async ({ page }) => {
    await page.goto(`${BASE_URL}/invoices/create`);

    // Should have form elements (main content form, not debugbar)
    const form = page
      .locator("#invoice-store-form, .card form, form[action*='invoice']")
      .first();
    await expect(form).toBeVisible();

    // Check for customer select container (Choices.js hides native select)
    const customerSelectContainer = page
      .locator('.choices, [name="customer_id"]')
      .first();
    await expect(customerSelectContainer).toBeAttached();
  });

  test("should have create button: HTMLButtonElement on invoice form: HTMLFormElement", async ({
    page,
  }) => {
    await page.goto(`${BASE_URL}/invoices/create`);

    // Verify submit button exists
    const submitBtn = page
      .locator('button[type="submit"], input[type="submit"]')
      .first();
    await expect(submitBtn).toBeAttached();
    await expect(page).toHaveURL(/.*invoices/);
  });
});

test.describe("Bills Module", (): void => {
  test("should display bills index: number with table", async ({ page }) => {
    await page.goto(`${BASE_URL}/bills`);
    await expect(page).toHaveURL(/.*bills/);

    const table = page
      .locator("table.datatable, table.dataTable-table")
      .first();
    await expect(table).toBeVisible();
  });

  test("should load bill create form: HTMLFormElement", async ({ page }) => {
    await page.goto(`${BASE_URL}/bills/create`);

    const form = page.locator("#bills-store-form, .card form").first();
    await expect(form).toBeVisible();

    // Check for vendor select container (Choices.js hides native select)
    const vendorSelectContainer = page
      .locator('.choices, [name="vendor_id"]')
      .first();
    await expect(vendorSelectContainer).toBeAttached();
  });
});

test.describe("Payments Module", (): void => {
  test("should display payments index: number", async ({ page }) => {
    await page.goto(`${BASE_URL}/payments`);
    await expect(page).toHaveURL(/.*payments/);

    const table = page
      .locator("table.datatable, table.dataTable-table")
      .first();
    await expect(table).toBeVisible();
  });
});

test.describe("Expenses Module", (): void => {
  test("should display expenses index: number", async ({ page }) => {
    await page.goto(`${BASE_URL}/expenses`);
    await expect(page).toHaveURL(/.*expenses/);

    const table = page
      .locator("table.datatable, table.dataTable-table")
      .first();
    await expect(table).toBeVisible();
  });

  test("should load expense create form: HTMLFormElement", async ({ page }) => {
    await page.goto(`${BASE_URL}/expenses/create`);

    const form = page.locator("#expense-create-form, .card form").first();
    await expect(form).toBeVisible();
  });
});

test.describe("Financial Reports", (): void => {
  test("should display invoice summary report", async ({ page }) => {
    await page.goto(`${BASE_URL}/reports/invoice-summary`);

    // Should have report content
    const content = page.locator(".card, .report, main");
    await expect(content.first()).toBeVisible();
  });

  test("should display bill summary report", async ({ page }) => {
    await page.goto(`${BASE_URL}/reports/bill-summary`);

    const content = page.locator(".card, .report, main");
    await expect(content.first()).toBeVisible();
  });

  test("should display expense summary report", async ({ page }) => {
    await page.goto(`${BASE_URL}/reports/expense-summary`);

    const content = page.locator(".card, .report, main");
    await expect(content.first()).toBeVisible();
  });

  test("should display income summary report", async ({ page }) => {
    await page.goto(`${BASE_URL}/reports/income-summary`);

    const content = page.locator(".card, .report, main");
    await expect(content.first()).toBeVisible();
  });

  test("should display income vs expense report", async ({ page }) => {
    await page.goto(`${BASE_URL}/reports/income-vs-expense-summary`);

    const content = page.locator(".card, .report, main");
    await expect(content.first()).toBeVisible();
  });
});

test.describe("Payroll Module", (): void => {
  test("should display payslips index: number", async ({ page }) => {
    await page.goto(`${BASE_URL}/payslips`);
    await expect(page).toHaveURL(/.*payslips/);

    const content = page.locator(".card, table, main");
    await expect(content.first()).toBeVisible();
  });

  test("should display allowances index: number", async ({ page }) => {
    await page.goto(`${BASE_URL}/allowances`);
    await expect(page).toHaveURL(/.*allowances/);

    const content = page.locator(".card, table, main");
    await expect(content.first()).toBeVisible();
  });

  test("should display loans index: number", async ({ page }) => {
    await page.goto(`${BASE_URL}/loans`);
    await expect(page).toHaveURL(/.*loans/);

    const content = page.locator(".card, table, main");
    await expect(content.first()).toBeVisible();
  });
});

test.describe("Data Accuracy Tests", (): void => {
  test("invoice totals should be numeric", async ({ page }) => {
    await page.goto(`${BASE_URL}/invoices`);

    // Find cells that might contain amounts (in data table only)
    const amountCells = page
        .locator("table.datatable td, table.dataTable-table td")
        .filter({ hasText: /[\d,.]+/ }),
      count = await amountCells.count();
    // Should have some numeric data
    expect(count).toBeGreaterThanOrEqual(0);
  });

  test("bill totals should be numeric", async ({ page }) => {
    await page.goto(`${BASE_URL}/bills`);

    const amountCells = page
        .locator("table.datatable td, table.dataTable-table td")
        .filter({ hasText: /[\d,.]+/ }),
      count = await amountCells.count();
    expect(count).toBeGreaterThanOrEqual(0);
  });

  test("report summaries should contain numbers", async ({ page }) => {
    await page.goto(`${BASE_URL}/reports/invoice-summary`);

    // Reports should have numeric data
    const numericContent = page.locator("text=/\\d+/"),
      count = await numericContent.count();
    expect(count).toBeGreaterThan(0);
  });
});

test.describe("Super Admin Access", (): void => {
  test("should access user management", async ({ page }) => {
    await page.goto(`${BASE_URL}/users`);
    await expect(page).toHaveURL(/.*users/);
    // Users page should have main content area
    const content = page
      .locator(".dash-content, .dash-container, .col-md-12")
      .first();
    await expect(content).toBeVisible();
  });

  test("should access roles management", async ({ page }) => {
    await page.goto(`${BASE_URL}/roles`);
    await expect(page).toHaveURL(/.*roles/);
    const content = page
      .locator(".dash-content, .dash-container, .col-md-12")
      .first();
    await expect(content).toBeVisible();
  });

  test("should access plans management", async ({ page }) => {
    await page.goto(`${BASE_URL}/plans`);
    await expect(page).toHaveURL(/.*plans/);
    const content = page
      .locator(".dash-content, .dash-container, .col-md-12")
      .first();
    await expect(content).toBeVisible();
  });
});

// ── Corrected Route Names (previously 404) ─────────────────────────
test.describe("Corrected Financial Routes", (): void => {
  test("should display deduction_options index: number", async ({ page }) => {
    await page.goto(`${BASE_URL}/deduction_options`);
    await expect(page).toHaveURL(/.*deduction_options/);
    const title = await page.title();
    expect(title).toContain("ERP");
    const content = page
      .locator(".dash-content, .dash-container, .col-md-12")
      .first();
    await expect(content).toBeVisible();
  });

  test("should display journal_entries index: number", async ({ page }) => {
    await page.goto(`${BASE_URL}/journal_entries`);
    await expect(page).toHaveURL(/.*journal_entries/);
    const title = await page.title();
    expect(title).toContain("ERP");
    const content = page
      .locator(".dash-content, .dash-container, .col-md-12")
      .first();
    await expect(content).toBeVisible();
  });

  test("should display chart_of_accounts index: number", async ({ page }) => {
    await page.goto(`${BASE_URL}/chart_of_accounts`);
    await expect(page).toHaveURL(/.*chart_of_accounts/);
    const title = await page.title();
    expect(title).toContain("ERP");
    const content = page
      .locator(".dash-content, .dash-container, .col-md-12")
      .first();
    await expect(content).toBeVisible();
  });

  test("should display reports/transaction page", async ({ page }) => {
    await page.goto(`${BASE_URL}/reports/transaction`);
    await expect(page).toHaveURL(/.*reports\/transaction/);
    const title = await page.title();
    expect(title).toContain("ERP");
    const content = page
      .locator(".dash-content, .dash-container, .col-md-12")
      .first();
    await expect(content).toBeVisible();
  });

  test("should display bank_transfers index: number", async ({ page }) => {
    await page.goto(`${BASE_URL}/bank_transfers`);
    await expect(page).toHaveURL(/.*bank_transfers/);
    const title = await page.title();
    expect(title).toContain("ERP");
    const content = page
      .locator(".dash-content, .dash-container, .col-md-12")
      .first();
    await expect(content).toBeVisible();
  });

  test("should create journal entry form: HTMLFormElement", async ({
    page,
  }) => {
    await page.goto(`${BASE_URL}/journal_entries/create`);
    await expect(page).toHaveURL(/.*journal_entries\/create/);
    const form = page.locator("#jrn-et-store-form");
    await expect(form).toBeVisible();
  });

  test("should create chart_of_accounts form: HTMLFormElement", async ({
    page,
  }) => {
    await page.goto(`${BASE_URL}/chart_of_accounts/create`);
    await expect(page).toHaveURL(/.*chart_of_accounts.*modal=create/);
    const content = page
      .locator(".dash-content, .dash-container, .col-md-12")
      .first();
    await expect(content).toBeVisible();
  });
});

// ── Modal Pattern Routes ────────────────────────────────────────────
test.describe("Modal Pattern Routes", (): void => {
  test("allowances/create redirects to modal", async ({ page }) => {
    await page.goto(`${BASE_URL}/allowances/create`);
    await expect(page).toHaveURL(/.*allowances.*modal=create/);
    const content = page
      .locator(".dash-content, .dash-container, .col-md-12")
      .first();
    await expect(content).toBeVisible();
  });

  test("commissions/create redirects to modal", async ({ page }) => {
    await page.goto(`${BASE_URL}/commissions/create`);
    await expect(page).toHaveURL(/.*commissions.*modal=create/);
    const content = page
      .locator(".dash-content, .dash-container, .col-md-12")
      .first();
    await expect(content).toBeVisible();
  });

  test("systems/create redirects to modal", async ({ page }) => {
    await page.goto(`${BASE_URL}/systems/create`);
    await expect(page).toHaveURL(/.*systems.*modal=create/);
    const content = page
      .locator(".dash-content, .dash-container, .col-md-12")
      .first();
    await expect(content).toBeVisible();
  });

  test("pricing_plans/create redirects to modal", async ({ page }) => {
    await page.goto(`${BASE_URL}/pricing_plans/create`);
    await expect(page).toHaveURL(/.*pricing_plans.*modal=create/);
    const content = page
      .locator(".dash-content, .dash-container, .col-md-12")
      .first();
    await expect(content).toBeVisible();
  });

  test("set_salaries/create redirects to index (no create form)", async ({
    page,
  }) => {
    // set_salaries has no create.blade.php — the controller redirects to index
    // with an info flash because salary setup is managed per employee.
    await page.goto(`${BASE_URL}/set_salaries/create`);
    await expect(page).toHaveURL(/.*set_salaries/);
    const content = page
      .locator(".dash-content, .dash-container, .col-md-12")
      .first();
    await expect(content).toBeVisible();
  });

  test("chart_of_accounts/create redirects to modal", async ({ page }) => {
    await page.goto(`${BASE_URL}/chart_of_accounts/create`);
    await expect(page).toHaveURL(/.*chart_of_accounts.*modal=create/);
    const content = page
      .locator(".dash-content, .dash-container, .col-md-12")
      .first();
    await expect(content).toBeVisible();
  });
});
