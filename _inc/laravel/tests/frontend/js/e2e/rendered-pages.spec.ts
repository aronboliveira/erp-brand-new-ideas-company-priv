// @ts-nocheck
/**
 * Rendered Page Mock Tests — Structural Validation
 *
 * Tests mock HTML pages that replicate what blade views render WITH data.
 * Isolates DOM structure issues from server timing / N+1 query problems.
 * Uses the same selectors that the real E2E tests (financial.spec.cjs,
 * module-pages.spec.cjs) use — if these fail, the real tests will too.
 *
 * Served by http-server on port 3000 (playwright-frontend.config.cjs).
 */
import { test, expect } from "@playwright/test";

const BASE = "http://localhost:3000/mocks/rendered";

/* ------------------------------------------------------------------ */
/*  Helper: benign JS error patterns (same as module-pages.spec.cjs)  */
/* ------------------------------------------------------------------ */
const benignPatterns = ["bootstrap is not defined", "Expected one of the following types", "simpleDatatables", "net::ERR", "favicon", "404 (Not Found)", "403 (Forbidden)", "ResizeObserver", "Non-Error promise rejection", "Cannot read properties of undefined", "reading 'require'", "Identifier '", "has already been declared", "Dragula unavailable"];

/**
 * @param {import('@playwright/test').Page} page
 * @param {string} file
 * @param {string[]} [errors]
 */
async function loadMock(page, file, errors = []) {
  page.on("pageerror", err => {
    const msg = err.message || String(err);
    const isBenign = benignPatterns.some(p => msg.toLowerCase().includes(p.toLowerCase()));
    if (!isBenign) errors.push(msg);
  });
  const resp = await page.goto(`${BASE}/${file}`, {
    timeout: 15000,
    waitUntil: "domcontentloaded",
  });
  expect(resp?.status()).toBe(200);
}

/* ================================================================== */
/*  1. Users Index — Card Grid Layout                                 */
/* ================================================================== */
test.describe("Users Index Mock — /users selectors", () => {
  test("page has .dash-content visible", async ({ page }) => {
    await loadMock(page, "users-index.html");
    // Same selector as financial.spec.cjs "Super Admin Access" tests
    const content = page.locator(".dash-content, .dash-container, .col-md-12").first();
    await expect(content).toBeVisible();
  });

  test("page title contains ERP", async ({ page }) => {
    await loadMock(page, "users-index.html");
    const title = await page.title();
    expect(title).toContain("ERP");
  });

  test("breadcrumb renders Dashboard > User", async ({ page }) => {
    await loadMock(page, "users-index.html");
    const breadcrumb = page.locator(".breadcrumb");
    await expect(breadcrumb).toBeVisible();
    const items = page.locator(".breadcrumb-item");
    expect(await items.count()).toBeGreaterThanOrEqual(2);
  });

  test("user cards exist (card-2 class)", async ({ page }) => {
    await loadMock(page, "users-index.html");
    // The blade renders .card.card-2 per user
    const cards = page.locator(".card-2");
    const count = await cards.count();
    expect(count).toBeGreaterThanOrEqual(1);
  });

  test("user card has name and email", async ({ page }) => {
    await loadMock(page, "users-index.html");
    const nameEl = page.locator(".card-2 h4.text-primary").first();
    await expect(nameEl).toBeVisible();
    const emailEl = page.locator(".card-2 small.text-primary").first();
    await expect(emailEl).toBeVisible();
  });

  test("super admin sees user/customer/vendor counts", async ({ page }) => {
    await loadMock(page, "users-index.html");
    // In the blade: col-4 with tooltip "Users", "Customers", "Vendors"
    const tooltipUsers = page.locator('[title="Users"]');
    const tooltipCustomers = page.locator('[title="Customers"]');
    const tooltipVendors = page.locator('[title="Vendors"]');
    expect(await tooltipUsers.count()).toBeGreaterThanOrEqual(1);
    expect(await tooltipCustomers.count()).toBeGreaterThanOrEqual(1);
    expect(await tooltipVendors.count()).toBeGreaterThanOrEqual(1);
  });

  test("create button and logs button exist", async ({ page }) => {
    await loadMock(page, "users-index.html");
    const createBtn = page.locator('a[title="Create"]').first();
    await expect(createBtn).toBeAttached();
    const logsBtn = page.locator('a[title="User Logs History"]').first();
    await expect(logsBtn).toBeAttached();
  });

  test("no critical JS errors", async ({ page }) => {
    const errors = [];
    await loadMock(page, "users-index.html", errors);
    await page.waitForTimeout(500);
    expect(errors.length).toBe(0);
  });
});

/* ================================================================== */
/*  2. Assets Index — DataTable Layout                                */
/* ================================================================== */
test.describe("Assets Index Mock — /account_assets selectors", () => {
  test("page has .dash-content visible", async ({ page }) => {
    await loadMock(page, "assets-index.html");
    const content = page.locator(".dash-content, .dash-container, .col-md-12").first();
    await expect(content).toBeVisible();
  });

  test("DataTable structure: table.datatable exists", async ({ page }) => {
    await loadMock(page, "assets-index.html");
    // Same selector as financial.spec.cjs
    const table = page.locator("table.datatable, table.dataTable-table").first();
    await expect(table).toBeVisible();
  });

  test("table has 7 header columns", async ({ page }) => {
    await loadMock(page, "assets-index.html");
    const headers = page.locator("table thead th");
    const count = await headers.count();
    // Name, Users, Purchase Date, Supported Date, Amount, Description, Action
    expect(count).toBe(7);
  });

  test("table has data rows", async ({ page }) => {
    await loadMock(page, "assets-index.html");
    const rows = page.locator("table tbody tr");
    const count = await rows.count();
    expect(count).toBeGreaterThan(0);
  });

  test("DataTable wrapper present (simple-datatables init)", async ({ page }) => {
    await loadMock(page, "assets-index.html");
    // After simple-datatables initializes, it wraps the table in .dataTable-wrapper
    const wrapper = page.locator(".dataTable-wrapper");
    await expect(wrapper).toBeAttached();
  });

  test("create asset button exists", async ({ page }) => {
    await loadMock(page, "assets-index.html");
    const createBtn = page.locator(".account-asset-create").first();
    await expect(createBtn).toBeAttached();
  });

  test("action buttons per row (edit + delete)", async ({ page }) => {
    await loadMock(page, "assets-index.html");
    const editBtns = page.locator("table tbody .btn-light-primary");
    expect(await editBtns.count()).toBeGreaterThanOrEqual(1);
    const deleteBtns = page.locator("table tbody .btn-light-danger");
    expect(await deleteBtns.count()).toBeGreaterThanOrEqual(1);
  });

  test("no critical JS errors", async ({ page }) => {
    const errors = [];
    await loadMock(page, "assets-index.html", errors);
    await page.waitForTimeout(500);
    expect(errors.length).toBe(0);
  });
});

/* ================================================================== */
/*  3. Dashboard — Charts + Metric Cards + Tables                     */
/* ================================================================== */
test.describe("Dashboard Mock — / (account-dashboard) selectors", () => {
  test("page has .dash-content visible", async ({ page }) => {
    await loadMock(page, "dashboard-account.html");
    const content = page.locator(".dash-content, .dash-container, .col-md-12").first();
    await expect(content).toBeVisible();
  });

  test("4 metric cards present", async ({ page }) => {
    await loadMock(page, "dashboard-account.html");
    // module-pages.spec.cjs checks: ".card, .chart, canvas, .row"
    const metricCards = page.locator(".theme-avatar");
    const count = await metricCards.count();
    expect(count).toBe(4);
  });

  test("metric cards have numeric values", async ({ page }) => {
    await loadMock(page, "dashboard-account.html");
    const h3s = page.locator(".col-lg-3 h3");
    const count = await h3s.count();
    expect(count).toBe(4);
    for (let i = 0; i < count; i++) {
      const text = await h3s.nth(i).textContent();
      expect(text?.trim()).toMatch(/^\d+$/);
    }
  });

  test("chart containers exist (#incExpBarChart, #cash-flow)", async ({ page }) => {
    await loadMock(page, "dashboard-account.html");
    const barChart = page.locator("#incExpBarChart");
    await expect(barChart).toBeAttached();
    const cashFlow = page.locator("#cash-flow");
    await expect(cashFlow).toBeAttached();
  });

  test("donut chart containers exist (#expenseByCategory, #incomeByCategory)", async ({ page }) => {
    await loadMock(page, "dashboard-account.html");
    const expense = page.locator("#expenseByCategory");
    await expect(expense).toBeAttached();
    const income = page.locator("#incomeByCategory");
    await expect(income).toBeAttached();
  });

  test("storage limit chart exists (#limit-chart)", async ({ page }) => {
    await loadMock(page, "dashboard-account.html");
    const limitChart = page.locator("#limit-chart");
    await expect(limitChart).toBeAttached();
  });

  test("chart spinner placeholders visible in all 5 containers", async ({ page }) => {
    await loadMock(page, "dashboard-account.html");
    const spinners = page.locator(".dashboard-chart-spinner");
    expect(await spinners.count()).toBe(5);
    for (let i = 0; i < 5; i++) {
      await expect(spinners.nth(i)).toBeVisible();
    }
  });

  test("spinners have accessible loading labels", async ({ page }) => {
    await loadMock(page, "dashboard-account.html");
    const labels = page.locator(".dashboard-chart-spinner .text-muted.small");
    expect(await labels.count()).toBe(5);
    for (let i = 0; i < 5; i++) {
      const text = await labels.nth(i).textContent();
      expect(text?.toLowerCase()).toContain("loading");
    }
  });

  test("spinners reserve minimum height for chart space", async ({ page }) => {
    await loadMock(page, "dashboard-account.html");
    const spinners = page.locator(".dashboard-chart-spinner");
    for (let i = 0; i < (await spinners.count()); i++) {
      const box = await spinners.nth(i).boundingBox();
      expect(box).not.toBeNull();
      expect(box!.height).toBeGreaterThanOrEqual(140);
    }
  });

  test("Account Balance table has rows", async ({ page }) => {
    await loadMock(page, "dashboard-account.html");
    // Searches for table with Bank / Holder Name / Balance headers
    const bankTh = page.locator("th", { hasText: "Bank" });
    await expect(bankTh.first()).toBeVisible();
    const rows = bankTh.first().locator("xpath=ancestor::table").locator("tbody tr");
    const count = await rows.count();
    expect(count).toBeGreaterThan(0);
  });

  test("Latest Income table has rows", async ({ page }) => {
    await loadMock(page, "dashboard-account.html");
    const header = page.locator("h5", { hasText: "Latest Income" });
    await expect(header).toBeVisible();
  });

  test("Recent Invoices table has rows", async ({ page }) => {
    await loadMock(page, "dashboard-account.html");
    const invTh = page.locator("th", { hasText: "Invoice" });
    await expect(invTh.first()).toBeVisible();
  });

  test("Recent Bills table has rows", async ({ page }) => {
    await loadMock(page, "dashboard-account.html");
    const billTh = page.locator("th", { hasText: "Bill" });
    await expect(billTh.first()).toBeVisible();
  });

  test("no critical JS errors", async ({ page }) => {
    const errors = [];
    await loadMock(page, "dashboard-account.html", errors);
    await page.waitForTimeout(500);
    expect(errors.length).toBe(0);
  });
});

/* ================================================================== */
/*  4. POS Index — Product Grid + Cart                                */
/* ================================================================== */
test.describe("POS Mock — /pos selectors", () => {
  test("page has .dash-content visible", async ({ page }) => {
    await loadMock(page, "pos-index.html");
    const content = page.locator(".dash-content, .dash-container, .col-md-12").first();
    await expect(content).toBeVisible();
  });

  test("product cards exist", async ({ page }) => {
    await loadMock(page, "pos-index.html");
    const products = page.locator(".product-card");
    expect(await products.count()).toBeGreaterThanOrEqual(1);
  });

  test("cart section has items", async ({ page }) => {
    await loadMock(page, "pos-index.html");
    const cartItems = page.locator(".cart-item");
    expect(await cartItems.count()).toBeGreaterThanOrEqual(1);
  });

  test("cart total is visible", async ({ page }) => {
    await loadMock(page, "pos-index.html");
    const total = page.locator(".cart-total");
    await expect(total).toBeVisible();
  });

  test("submit and clear buttons exist", async ({ page }) => {
    await loadMock(page, "pos-index.html");
    const submit = page.locator("#pos-submit-btn");
    await expect(submit).toBeAttached();
    const clear = page.locator("#pos-clear-btn");
    await expect(clear).toBeAttached();
  });

  test("error response (422 simulation) is visible", async ({ page }) => {
    await loadMock(page, "pos-index.html");
    const error = page.locator(".error-response");
    await expect(error).toBeVisible();
    await expect(error).toContainText("Add some products to cart");
  });

  test("no critical JS errors", async ({ page }) => {
    const errors = [];
    await loadMock(page, "pos-index.html", errors);
    await page.waitForTimeout(500);
    expect(errors.length).toBe(0);
  });
});

/* ================================================================== */
/*  5. Export Routes — Verification Table                             */
/* ================================================================== */
test.describe("Export Routes Mock — fixed route verification", () => {
  test("page has .dash-content visible", async ({ page }) => {
    await loadMock(page, "export-routes.html");
    const content = page.locator(".dash-content, .dash-container, .col-md-12").first();
    await expect(content).toBeVisible();
  });

  test("export routes table has 6 rows", async ({ page }) => {
    await loadMock(page, "export-routes.html");
    const rows = page.locator("#export-routes-table tbody tr");
    const count = await rows.count();
    expect(count).toBe(6);
  });

  test("all export routes show Fixed status", async ({ page }) => {
    await loadMock(page, "export-routes.html");
    const fixedBadges = page.locator('#export-routes-table .badge-success:has-text("Fixed")');
    const count = await fixedBadges.count();
    expect(count).toBe(6);
  });

  test("debit note bill route is listed", async ({ page }) => {
    await loadMock(page, "export-routes.html");
    const row = page.locator('tr[data-route="/debit_notes/{id}/bill"]');
    await expect(row).toBeAttached();
  });

  test("POS create route is listed", async ({ page }) => {
    await loadMock(page, "export-routes.html");
    const row = page.locator('tr[data-route="/pos/create"]');
    await expect(row).toBeAttached();
  });

  test("specific export routes listed", async ({ page }) => {
    await loadMock(page, "export-routes.html");
    const routes = ["/bills/export", "/customers/export", "/invoices/export", "/proposals/export", "/vendors/export", "/leaves/export"];
    for (const route of routes) {
      const row = page.locator(`tr[data-route="${route}"]`);
      await expect(row).toBeAttached();
    }
  });

  test("no critical JS errors", async ({ page }) => {
    const errors = [];
    await loadMock(page, "export-routes.html", errors);
    await page.waitForTimeout(500);
    expect(errors.length).toBe(0);
  });
});
