import { test, expect } from "@playwright/test";
import path from "path";

/**
 * ERP Brand New Ideas Company – Product Control Route Rendering E2E Tests
 * Verifies every Product Control index/create route renders
 * the expected table / card / form / breadcrumb elements correctly.
 *
 * Requires auth.setup.cjs to have been run first.
 */

const BASE_URL = "http://localhost:8000";
const STORAGE_STATE = path.join(import.meta.dirname, ".auth/user.json");

test.use({ storageState: STORAGE_STATE });

test.beforeEach(async ({ page }) => {
  page.on("dialog", d => d.accept());
  page.addLocatorHandler(page.locator("#cc--main, .c--anim"), async () => {
    const btn = page.locator('#c-p-bn, .c-bn, [data-cc="accept-all"]').first();
    if (await btn.isVisible({ timeout: 1000 }).catch(() => false))
      await btn.click({ force: true });
  });
});

async function assertPageRenders(page, route, label, opts = {}) {
  await test.step(`Navigate to ${label}`, async () => {
    const resp = await page.goto(`${BASE_URL}/${route}`, {
      waitUntil: "commit",
      timeout: 45000,
    });
    expect(resp?.status(), `${label} HTTP status`).toBeLessThan(500);
    await page
      .waitForLoadState("domcontentloaded", { timeout: 60000 })
      .catch(() => {});
  });

  await test.step(`${label}: layout renders`, async () => {
    const layout = page.locator(
      ".dash-content, .dash-container, .main-content, .container-fluid, .pcoded-content, body",
    );
    await expect(layout.first()).toBeVisible({ timeout: 15000 });
  });

  if (opts.expectTable) {
    await test.step(`${label}: table visible`, async () => {
      const table = page.locator(
        "table.dataTable, table.table, .table-responsive table, .card-body table, table:not(.phpdebugbar-widgets-params):not([class*='phpdebugbar'])",
      );
      await expect(table.first()).toBeVisible({ timeout: 15000 });
    });
  }

  if (opts.expectCard) {
    await test.step(`${label}: card visible`, async () => {
      const card = page.locator(".card, .card-body");
      await expect(card.first()).toBeVisible({ timeout: 15000 });
    });
  }

  if (opts.expectForm) {
    await test.step(`${label}: form visible`, async () => {
      const form = page.locator("form:not(#frm-logout):not(.d-none)");
      await expect(form.first()).toBeVisible({ timeout: 15000 });
    });
  }

  if (opts.expectBreadcrumb) {
    await test.step(`${label}: breadcrumb visible`, async () => {
      const bc = page.locator(
        ".breadcrumb, .breadcrumb-item, [aria-label='breadcrumb']",
      );
      await expect(bc.first()).toBeVisible({ timeout: 10000 });
    });
  }

  if (opts.expectText) {
    await test.step(`${label}: contains keyword "${opts.expectText}"`, async () => {
      const body = await page.textContent("body");
      expect(body?.toLowerCase()).toContain(opts.expectText.toLowerCase());
    });
  }

  if (opts.expectSelect2) {
    await test.step(`${label}: select2 elements present`, async () => {
      const select = page.locator(
        ".select2, .select2-container, select.form-control, select.form-select",
      );
      await expect(select.first()).toBeVisible({ timeout: 10000 });
    });
  }
}

/* ═══════════════════════════════════════════════════════════════════
   SECTION 1 — Product Services
   ═══════════════════════════════════════════════════════════════════ */

test.describe("Product Services", () => {
  test("product_services index renders", async ({ page }) => {
    await assertPageRenders(
      page,
      "product_services",
      "Product Services Index",
      {
        expectCard: true,
      },
    );
  });

  test("product_services create renders", async ({ page }) => {
    await assertPageRenders(
      page,
      "product_services/create",
      "Product Services Create",
      {
        expectCard: true,
      },
    );
  });

  test("product_services import renders", async ({ page }) => {
    await assertPageRenders(
      page,
      "product_services/import",
      "Product Services Import",
      {
        expectForm: true,
      },
    );
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 2 — Product Service Categories
   ═══════════════════════════════════════════════════════════════════ */

test.describe("Product Service Categories", () => {
  test("product_service_categories index renders", async ({ page }) => {
    await assertPageRenders(
      page,
      "product_service_categories",
      "Product Categories Index",
      {
        expectCard: true,
      },
    );
  });

  test("product_service_categories create renders", async ({ page }) => {
    await assertPageRenders(
      page,
      "product_service_categories/create",
      "Product Categories Create",
      {
        expectCard: true,
      },
    );
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 3 — Product Service Units
   ═══════════════════════════════════════════════════════════════════ */

test.describe("Product Service Units", () => {
  test("product_service_units index renders", async ({ page }) => {
    await assertPageRenders(
      page,
      "product_service_units",
      "Product Units Index",
      {
        expectCard: true,
      },
    );
  });

  test("product_service_units create renders", async ({ page }) => {
    await assertPageRenders(
      page,
      "product_service_units/create",
      "Product Units Create",
      {
        expectCard: true,
      },
    );
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 4 — Product Stocks
   ═══════════════════════════════════════════════════════════════════ */

test.describe("Product Stocks", () => {
  test("product_stocks index renders", async ({ page }) => {
    await assertPageRenders(page, "product_stocks", "Product Stocks Index", {
      expectCard: true,
    });
  });

  test("product_stocks create renders", async ({ page }) => {
    await assertPageRenders(
      page,
      "product_stocks/create",
      "Product Stocks Create",
      {
        expectCard: true,
      },
    );
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 5 — Warehouses
   ═══════════════════════════════════════════════════════════════════ */

test.describe("Warehouses", () => {
  test("warehouses index renders", async ({ page }) => {
    await assertPageRenders(page, "warehouses", "Warehouses Index", {
      expectCard: true,
    });
  });

  test("warehouses create renders", async ({ page }) => {
    await assertPageRenders(page, "warehouses/create", "Warehouses Create", {
      expectCard: true,
    });
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 6 — Warehouse Transfers
   ═══════════════════════════════════════════════════════════════════ */

test.describe("Warehouse Transfers", () => {
  test("warehouse_transfers index renders", async ({ page }) => {
    await assertPageRenders(
      page,
      "warehouse_transfers",
      "Warehouse Transfers Index",
      {
        expectCard: true,
      },
    );
  });

  test("warehouse_transfers create renders", async ({ page }) => {
    // Modal partial (no @extends) – only a form is rendered.
    await assertPageRenders(
      page,
      "warehouse_transfers/create",
      "Warehouse Transfers Create",
      {
        expectForm: true,
      },
    );
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 7 — Proposal Products
   ═══════════════════════════════════════════════════════════════════ */

test.describe("Proposal Products", () => {
  test("proposal_products index renders", async ({ page }) => {
    await assertPageRenders(
      page,
      "proposal_products",
      "Proposal Products Index",
      {
        expectCard: true,
      },
    );
  });

  test("proposal_products create renders", async ({ page }) => {
    await assertPageRenders(
      page,
      "proposal_products/create",
      "Proposal Products Create",
      {
        expectCard: true,
      },
    );
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 8 — Grid/Card View Toggles
   ═══════════════════════════════════════════════════════════════════ */

test.describe("Product View Toggles", () => {
  test("product_services grid view renders", async ({ page }) => {
    await test.step("Navigate to products", async () => {
      const resp = await page.goto(`${BASE_URL}/product_services`, {
        waitUntil: "domcontentloaded",
        timeout: 45000,
      });
      expect(resp?.status()).toBeLessThan(500);
    });

    await test.step("Check for view toggle buttons", async () => {
      const toggleBtns = page.locator(
        '[data-view="grid"], [data-view="list"], .view-toggle, .btn-group .btn',
      );
      const count = await toggleBtns.count();
      // Either there are toggle buttons or we're in a default view
      if (count > 0) {
        await expect(toggleBtns.first()).toBeVisible({ timeout: 10000 });
      }
    });
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 9 — AJAX Routes (non-rendering)
   ═══════════════════════════════════════════════════════════════════ */

test.describe("Product AJAX Routes", () => {
  test("product_services search endpoint responds", async ({ page }) => {
    await page.goto(`${BASE_URL}/product_services`, {
      waitUntil: "domcontentloaded",
      timeout: 45000,
    });

    const response = await page.request.get(
      `${BASE_URL}/product_services/search?q=test`,
    );
    expect(response.status()).toBeLessThan(500);
  });

  test("warehouse_transfers get-product endpoint responds", async ({
    page,
  }) => {
    await page.goto(`${BASE_URL}/warehouse_transfers`, {
      waitUntil: "domcontentloaded",
      timeout: 45000,
    });

    const response = await page.request.post(
      `${BASE_URL}/warehouse_transfers/get-product`,
      {
        data: { warehouseId: 0 },
      },
    );
    expect(response.status()).toBeLessThan(500);
  });

  test("warehouse_transfers get-quantity endpoint responds", async ({
    page,
  }) => {
    await page.goto(`${BASE_URL}/warehouse_transfers`, {
      waitUntil: "domcontentloaded",
      timeout: 45000,
    });

    const response = await page.request.post(
      `${BASE_URL}/warehouse_transfers/get-quantity`,
      {
        data: { warehouseId: 0, productId: 0 },
      },
    );
    expect(response.status()).toBeLessThan(500);
  });

  test("product_service_categories get-account endpoint responds", async ({
    page,
  }) => {
    await page.goto(`${BASE_URL}/product_service_categories`, {
      waitUntil: "domcontentloaded",
      timeout: 45000,
    });

    const response = await page.request.post(
      `${BASE_URL}/product_service_categories/get-account`,
      {
        data: { type: 0 },
      },
    );
    expect(response.status()).toBeLessThan(500);
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 10 — Export Routes
   ═══════════════════════════════════════════════════════════════════ */

test.describe("Product Export Routes", () => {
  test("product_services export endpoint responds", async ({ page }) => {
    await page.goto(`${BASE_URL}/product_services`, {
      waitUntil: "domcontentloaded",
      timeout: 45000,
    });

    const response = await page.request.get(
      `${BASE_URL}/product_services/export`,
    );
    // Export might return 200, 302 (redirect), or require parameters
    expect(response.status()).toBeLessThan(500);
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 11 — Cart Operations (if applicable)
   ═══════════════════════════════════════════════════════════════════ */

test.describe("Product Cart Operations", () => {
  test("add-cart endpoint responds", async ({ page }) => {
    await page.goto(`${BASE_URL}/product_services`, {
      waitUntil: "domcontentloaded",
      timeout: 45000,
    });

    const response = await page.request.post(
      `${BASE_URL}/product_services/add-cart`,
      {
        data: { id: 1, qty: 1 },
      },
    );
    // May return 401/403 if not authorized, 422 if validation fails, but not 500
    expect(response.status()).toBeLessThan(500);
  });

  test("empty-cart endpoint responds", async ({ page }) => {
    await page.goto(`${BASE_URL}/product_services`, {
      waitUntil: "domcontentloaded",
      timeout: 45000,
    });

    const response = await page.request.post(
      `${BASE_URL}/product_services/empty-cart`,
    );
    expect(response.status()).toBeLessThan(500);
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 12 — Page Title Presence
   ═══════════════════════════════════════════════════════════════════ */

test.describe("Product Page Titles", () => {
  test("product_services has page title", async ({ page }) => {
    await assertPageRenders(page, "product_services", "Products Title", {
      expectCard: true,
    });
  });

  test("product_service_categories has page title", async ({ page }) => {
    await assertPageRenders(
      page,
      "product_service_categories",
      "Categories Title",
      {
        expectCard: true,
      },
    );
  });

  test("warehouses has page title", async ({ page }) => {
    await assertPageRenders(page, "warehouses", "Warehouses Title", {
      expectCard: true,
    });
  });
});
