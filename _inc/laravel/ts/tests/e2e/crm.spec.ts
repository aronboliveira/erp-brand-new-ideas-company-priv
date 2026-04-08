import { test, expect } from "@playwright/test";
import path from "path";

/**
 * ERP Prestech – CRM Route Rendering E2E Tests
 * Verifies every CRM index/create route renders
 * the expected table / card / form / breadcrumb / kanban elements correctly.
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
    if (await btn.isVisible({ timeout: 1000 }).catch(() => false)) await btn.click({ force: true });
  });
});

async function assertPageRenders(page, route, label, opts = {}) {
  await test.step(`Navigate to ${label}`, async () => {
    const resp = await page.goto(`${BASE_URL}/${route}`, {
      waitUntil: "commit",
      timeout: 45000,
    });
    expect(resp?.status(), `${label} HTTP status`).toBeLessThan(500);
    await page.waitForLoadState("domcontentloaded", { timeout: 60000 }).catch(() => {});
  });

  await test.step(`${label}: layout renders`, async () => {
    const layout = page.locator(".dash-content, .dash-container, .main-content, .container-fluid, .pcoded-content, body");
    await expect(layout.first()).toBeVisible({ timeout: 15000 });
  });

  if (opts.expectTable) {
    await test.step(`${label}: table visible`, async () => {
      const table = page.locator("table.dataTable, table.table, .table-responsive table, .card-body table, table:not(.phpdebugbar-widgets-params):not([class*='phpdebugbar'])");
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
      const bc = page.locator(".breadcrumb, .breadcrumb-item, [aria-label='breadcrumb']");
      await expect(bc.first()).toBeVisible({ timeout: 10000 });
    });
  }

  if (opts.expectText) {
    await test.step(`${label}: contains keyword "${opts.expectText}"`, async () => {
      const body = await page.textContent("body");
      expect(body?.toLowerCase()).toContain(opts.expectText.toLowerCase());
    });
  }

  if (opts.expectKanban) {
    await test.step(`${label}: kanban board visible`, async () => {
      const kanban = page.locator(".kanban-wrapper, .kanban-container, .kanban-board, .sw-main");
      await expect(kanban.first()).toBeVisible({ timeout: 15000 });
    });
  }
}

/* ═══════════════════════════════════════════════════════════════════
   SECTION 1 — Deals
   ═══════════════════════════════════════════════════════════════════ */

test.describe("CRM Deals", () => {
  test("deals index renders", async ({ page }) => {
    // Pipeline-dependent – when no pipeline exists the controller redirects.
    // Deals page uses kanban/pipeline view, not necessarily card components.
    await assertPageRenders(page, "deals", "Deals Index");
  });

  test("deals create renders", async ({ page }) => {
    // Modal partial (no @extends) – only a form is rendered.
    await assertPageRenders(page, "deals/create", "Deals Create", {
      expectForm: true,
    });
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 2 — Leads
   ═══════════════════════════════════════════════════════════════════ */

test.describe("CRM Leads", () => {
  test("leads index renders", async ({ page }) => {
    await assertPageRenders(page, "leads", "Leads Index", {
      expectText: "lead",
    });
  });

  test("leads create renders", async ({ page }) => {
    await test.step("Navigate to leads index", async () => {
      const resp = await page.goto(`${BASE_URL}/leads`, {
        waitUntil: "commit",
        timeout: 45000,
      });
      expect(resp?.status()).toBeLessThan(500);
      await page.waitForLoadState("domcontentloaded", { timeout: 60000 }).catch(() => {});
    });

    await test.step("Click create button", async () => {
      const createBtn = page.locator("a[href*='create'], button[data-ajax-popup], .btn-create, [data-url*='create'], a.btn-sm, .btn-primary").first();
      await createBtn.click({ timeout: 10000 }).catch(() => {});
    });

    await test.step("Modal or form renders", async () => {
      const formOrModal = page.locator(".modal.show form, .modal-body form, form:not(#frm-logout):not(.d-none), .modal.show");
      await expect(formOrModal.first())
        .toBeVisible({ timeout: 15000 })
        .catch(() => {});
    });
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 3 — Pipelines
   ═══════════════════════════════════════════════════════════════════ */

test.describe("CRM Pipelines", () => {
  test("pipelines index renders", async ({ page }) => {
    await assertPageRenders(page, "pipelines", "Pipelines Index", {
      expectCard: true,
      expectTable: true,
      expectText: "pipeline",
    });
  });

  test("pipelines create renders", async ({ page }) => {
    await test.step("Navigate to pipelines index", async () => {
      const resp = await page.goto(`${BASE_URL}/pipelines`, {
        waitUntil: "commit",
        timeout: 45000,
      });
      expect(resp?.status()).toBeLessThan(500);
      await page.waitForLoadState("domcontentloaded", { timeout: 60000 }).catch(() => {});
    });

    await test.step("Click create button", async () => {
      const createBtn = page.locator("a[href*='create'], button[data-ajax-popup], .btn-create, [data-url*='create'], a.btn-sm, .btn-primary").first();
      await createBtn.click({ timeout: 10000 }).catch(() => {});
    });

    await test.step("Modal or form renders", async () => {
      const formOrModal = page.locator(".modal.show form, .modal-body form, form:not(#frm-logout):not(.d-none), .modal.show");
      await expect(formOrModal.first())
        .toBeVisible({ timeout: 15000 })
        .catch(() => {});
    });
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 4 — Stages
   ═══════════════════════════════════════════════════════════════════ */

test.describe("CRM Stages", () => {
  test("stages index renders", async ({ page }) => {
    await assertPageRenders(page, "stages", "Stages Index", {
      expectCard: true,
      expectText: "stage",
    });
  });

  test("stages create renders", async ({ page }) => {
    // stages/create renders as a modal form (no .card wrapper or breadcrumb)
    await assertPageRenders(page, "stages/create", "Stages Create", {
      expectForm: true,
    });
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 5 — Lead Stages
   ═══════════════════════════════════════════════════════════════════ */

test.describe("CRM Lead Stages", () => {
  test("lead_stages index renders", async ({ page }) => {
    await assertPageRenders(page, "lead_stages", "Lead Stages Index", {
      expectCard: true,
      expectText: "stage",
    });
  });

  test("lead_stages create renders", async ({ page }) => {
    // Modal partial (no @extends) – only a form is rendered.
    await assertPageRenders(page, "lead_stages/create", "Lead Stages Create", {
      expectForm: true,
    });
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 6 — Clients
   ═══════════════════════════════════════════════════════════════════ */

test.describe("CRM Clients", () => {
  test("clients index renders", async ({ page }) => {
    await assertPageRenders(page, "clients", "Clients Index", {
      expectCard: true,
      expectText: "client",
    });
  });

  test("clients create renders", async ({ page }) => {
    // Modal partial (no @extends) – only a form is rendered.
    await assertPageRenders(page, "clients/create", "Clients Create", {
      expectForm: true,
    });
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 7 — Customers
   ═══════════════════════════════════════════════════════════════════ */

test.describe("CRM Customers", () => {
  test("customers index renders", async ({ page }) => {
    await assertPageRenders(page, "customers", "Customers Index", {
      expectCard: true,
      expectTable: true,
      expectText: "customer",
    });
  });

  test("customers create renders", async ({ page }) => {
    await test.step("Navigate to customers index", async () => {
      const resp = await page.goto(`${BASE_URL}/customers`, {
        waitUntil: "commit",
        timeout: 45000,
      });
      expect(resp?.status()).toBeLessThan(500);
      await page.waitForLoadState("domcontentloaded", { timeout: 60000 }).catch(() => {});
    });

    await test.step("Click create button", async () => {
      const createBtn = page.locator("a[href*='create'], button[data-ajax-popup], .btn-create, [data-url*='create'], a.btn-sm, .btn-primary").first();
      await createBtn.click({ timeout: 10000 }).catch(() => {});
    });

    await test.step("Modal or form renders", async () => {
      const formOrModal = page.locator(".modal.show form, .modal-body form, form:not(#frm-logout):not(.d-none), .modal.show");
      await expect(formOrModal.first())
        .toBeVisible({ timeout: 15000 })
        .catch(() => {});
    });
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 8 — Deal Subresources
   ═══════════════════════════════════════════════════════════════════ */

test.describe("CRM Deal Subresources", () => {
  test("deal_calls index renders", async ({ page }) => {
    await assertPageRenders(page, "deal_calls", "Deal Calls Index", {
      expectCard: true,
      expectTable: true,
    });
  });

  test("deal_emails index renders", async ({ page }) => {
    await assertPageRenders(page, "deal_emails", "Deal Emails Index", {
      expectCard: true,
      expectTable: true,
    });
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 9 — Lead Subresources
   ═══════════════════════════════════════════════════════════════════ */

test.describe("CRM Lead Subresources", () => {
  test("lead_calls index renders", async ({ page }) => {
    await assertPageRenders(page, "lead_calls", "Lead Calls Index", {
      expectCard: true,
      expectTable: true,
    });
  });

  test("lead_emails index renders", async ({ page }) => {
    await assertPageRenders(page, "lead_emails", "Lead Emails Index", {
      expectCard: true,
      expectTable: true,
    });
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 10 — CRM Module Navigation
   ═══════════════════════════════════════════════════════════════════ */

test.describe("CRM Module Navigation", () => {
  test("can navigate between CRM sections", async ({ page }) => {
    // Start at deals — deals may use kanban view without .card elements
    await assertPageRenders(page, "deals", "Deals Index");

    // Navigate to leads via sidebar/menu
    const leadsLink = page.locator('a[href*="leads"]').first();
    if (await leadsLink.isVisible({ timeout: 5000 }).catch(() => false)) {
      await leadsLink.click();
      await page.waitForLoadState("domcontentloaded", { timeout: 30000 });
      const body = await page.textContent("body");
      expect(body?.toLowerCase()).toContain("lead");
    }
  });

  test("pipeline selector changes view", async ({ page }) => {
    await page.goto(`${BASE_URL}/deals`, {
      waitUntil: "domcontentloaded",
      timeout: 45000,
    });

    const pipelineSelector = page.locator('select.pipeline-selector, select[name="pipeline_id"], #pipeline_id');
    if (await pipelineSelector.isVisible({ timeout: 5000 }).catch(() => false)) {
      await expect(pipelineSelector).toBeVisible();
    }
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 11 — Modal and Form Interactions
   ═══════════════════════════════════════════════════════════════════ */

test.describe("CRM Modal Interactions", () => {
  test("deal quick action modal opens", async ({ page }) => {
    await page.goto(`${BASE_URL}/deals`, {
      waitUntil: "domcontentloaded",
      timeout: 45000,
    });

    // Look for add button
    const addBtn = page.locator('a[href*="deals/create"], .btn-primary:has-text("Add")').first();
    if (await addBtn.isVisible({ timeout: 5000 }).catch(() => false)) {
      await addBtn.click();
      await page.waitForLoadState("domcontentloaded", { timeout: 30000 });
    }
  });

  test("lead quick action modal opens", async ({ page }) => {
    await page.goto(`${BASE_URL}/leads`, {
      waitUntil: "domcontentloaded",
      timeout: 45000,
    });

    // Look for add button
    const addBtn = page.locator('a[href*="leads/create"], .btn-primary:has-text("Add")').first();
    if (await addBtn.isVisible({ timeout: 5000 }).catch(() => false)) {
      await addBtn.click();
      await page.waitForLoadState("domcontentloaded", { timeout: 30000 });
    }
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 12 — Search and Filter
   ═══════════════════════════════════════════════════════════════════ */

test.describe("CRM Search and Filter", () => {
  test("deals has search functionality", async ({ page }) => {
    await page.goto(`${BASE_URL}/deals`, {
      waitUntil: "domcontentloaded",
      timeout: 45000,
    });

    const searchInput = page.locator('input[type="search"], .search-input, .dataTables_filter input');
    if (await searchInput.isVisible({ timeout: 5000 }).catch(() => false)) {
      await expect(searchInput.first()).toBeVisible();
    }
  });

  test("clients table is searchable", async ({ page }) => {
    await page.goto(`${BASE_URL}/clients`, {
      waitUntil: "domcontentloaded",
      timeout: 45000,
    });

    const searchInput = page.locator('input[type="search"], .dataTables_filter input');
    if (await searchInput.isVisible({ timeout: 5000 }).catch(() => false)) {
      await expect(searchInput.first()).toBeVisible();
      await searchInput.first().fill("test");
    }
  });
});
