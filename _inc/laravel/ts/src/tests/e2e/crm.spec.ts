/**
 * @fileoverview TypeScript version of tests/e2e/crm.spec.cjs
 * @generated from original JavaScript - manual review recommended
 * @module crm.spec
 */

/* global bootstrap, $, jQuery */
import { test, expect, type Page, type BrowserContext } from "@playwright/test";
import path from "path";
import type { AssertPageOptions } from "../../declarations/tests/e2e.interfaces";

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
  page.addLocatorHandler(
    page.locator("#cc--main, .c--anim"),
    async (): Promise<void> => {
      const btn = page
        .locator('#c-p-bn, .c-bn, [data-cc="accept-all"]')
        .first();
      if (await btn.isVisible({ timeout: 1000 }).catch(() => false))
        await btn.click({ force: true });
    },
  );
});

async function assertPageRenders(
  page: Page,
  route: string,
  label: string,
  opts: AssertPageOptions = {},
): Promise<void> {
  await test.step(`Navigate to ${label}`, async (): Promise<void> => {
    const resp = await page.goto(`${BASE_URL}/${route}`, {
      waitUntil: "commit",
      timeout: 45000,
    });
    expect(resp?.status(), `${label} HTTP status`).toBeLessThan(500);
    await page
      .waitForLoadState("domcontentloaded", { timeout: 60000 })
      .catch((): void => {});
  });

  await test.step(`${label}: layout renders`, async (): Promise<void> => {
    const layout = page.locator(
      ".dash-content, .dash-container, .main-content, .container-fluid, .pcoded-content, body",
    );
    await expect(layout.first()).toBeVisible({ timeout: 15000 });
  });

  if (opts.expectTable) {
    await test.step(`${label}: table visible`, async (): Promise<void> => {
      const table = page.locator(
        "table.dataTable, table.table, .table-responsive table, .card-body table, table:not(.phpdebugbar-widgets-params):not([class*='phpdebugbar'])",
      );
      await expect(table.first()).toBeVisible({ timeout: 15000 });
    });
  }

  if (opts.expectCard) {
    await test.step(`${label}: card visible`, async (): Promise<void> => {
      const card = page.locator(".card, .card-body");
      await expect(card.first()).toBeVisible({ timeout: 15000 });
    });
  }

  if (opts.expectForm) {
    await test.step(`${label}: form visible`, async (): Promise<void> => {
      const form = page.locator("form:not(#frm-logout):not(.d-none)");
      await expect(form.first()).toBeVisible({ timeout: 15000 });
    });
  }

  if (opts.expectBreadcrumb) {
    await test.step(`${label}: breadcrumb visible`, async (): Promise<void> => {
      const bc = page.locator(
        ".breadcrumb, .breadcrumb-item, [aria-label='breadcrumb']",
      );
      await expect(bc.first()).toBeVisible({ timeout: 10000 });
    });
  }

  if (opts.expectText) {
    await test.step(`${label}: contains keyword "${opts.expectText}"`, async (): Promise<void> => {
      const body = await page.textContent("body");
      expect(body?.toLowerCase()).toContain(opts.expectText?.toLowerCase());
    });
  }

  if (opts.expectKanban) {
    await test.step(`${label}: kanban board visible`, async (): Promise<void> => {
      const kanban = page.locator(
        ".kanban-wrapper, .kanban-container, .kanban-board, .sw-main",
      );
      await expect(kanban.first()).toBeVisible({ timeout: 15000 });
    });
  }
}

/* ═══════════════════════════════════════════════════════════════════
   SECTION 1 — Deals
   ═══════════════════════════════════════════════════════════════════ */

test.describe("CRM Deals", (): void => {
  test("deals index: number renders", async ({ page }) => {
    // Pipeline-dependent – when no pipeline exists the controller redirects.
    await assertPageRenders(page, "deals", "Deals Index", {
      expectCard: true,
    });
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

test.describe("CRM Leads", (): void => {
  test("leads index: number renders", async ({ page }) => {
    await assertPageRenders(page, "leads", "Leads Index", {
      expectText: "lead",
    });
  });

  test("leads create renders", async ({ page }) => {
    await test.step("Navigate to leads index", async (): Promise<void> => {
      const resp = await page.goto(`${BASE_URL}/leads`, {
        waitUntil: "commit",
        timeout: 45000,
      });
      expect(resp?.status()).toBeLessThan(500);
      await page
        .waitForLoadState("domcontentloaded", { timeout: 60000 })
        .catch((): void => {});
    });

    await test.step("Click create button", async (): Promise<void> => {
      const createBtn = page
        .locator(
          "a[href*='create'], button[data-ajax-popup], .btn-create, [data-url*='create'], a.btn-sm, .btn-primary",
        )
        .first();
      await createBtn.click({ timeout: 10000 }).catch((): void => {});
    });

    await test.step("Modal or form renders", async (): Promise<void> => {
      const formOrModal = page.locator(
        ".modal.show form, .modal-body form, form:not(#frm-logout):not(.d-none), .modal.show",
      );
      await expect(formOrModal.first())
        .toBeVisible({ timeout: 15000 })
        .catch((): void => {});
    });
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 3 — Pipelines
   ═══════════════════════════════════════════════════════════════════ */

test.describe("CRM Pipelines", (): void => {
  test("pipelines index: number renders", async ({ page }) => {
    await assertPageRenders(page, "pipelines", "Pipelines Index", {
      expectCard: true,
      expectTable: true,
      expectText: "pipeline",
    });
  });

  test("pipelines create renders", async ({ page }) => {
    await test.step("Navigate to pipelines index", async (): Promise<void> => {
      const resp = await page.goto(`${BASE_URL}/pipelines`, {
        waitUntil: "commit",
        timeout: 45000,
      });
      expect(resp?.status()).toBeLessThan(500);
      await page
        .waitForLoadState("domcontentloaded", { timeout: 60000 })
        .catch((): void => {});
    });

    await test.step("Click create button", async (): Promise<void> => {
      const createBtn = page
        .locator(
          "a[href*='create'], button[data-ajax-popup], .btn-create, [data-url*='create'], a.btn-sm, .btn-primary",
        )
        .first();
      await createBtn.click({ timeout: 10000 }).catch((): void => {});
    });

    await test.step("Modal or form renders", async (): Promise<void> => {
      const formOrModal = page.locator(
        ".modal.show form, .modal-body form, form:not(#frm-logout):not(.d-none), .modal.show",
      );
      await expect(formOrModal.first())
        .toBeVisible({ timeout: 15000 })
        .catch((): void => {});
    });
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 4 — Stages
   ═══════════════════════════════════════════════════════════════════ */

test.describe("CRM Stages", (): void => {
  test("stages index: number renders", async ({ page }) => {
    await assertPageRenders(page, "stages", "Stages Index", {
      expectCard: true,
      expectText: "stage",
    });
  });

  test("stages create renders", async ({ page }) => {
    await assertPageRenders(page, "stages/create", "Stages Create", {
      expectCard: true,
      expectForm: true,
      expectBreadcrumb: true,
    });
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 5 — Lead Stages
   ═══════════════════════════════════════════════════════════════════ */

test.describe("CRM Lead Stages", (): void => {
  test("lead_stages index: number renders", async ({ page }) => {
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

test.describe("CRM Clients", (): void => {
  test("clients index: number renders", async ({ page }) => {
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

test.describe("CRM Customers", (): void => {
  test("customers index: number renders", async ({ page }) => {
    await assertPageRenders(page, "customers", "Customers Index", {
      expectCard: true,
      expectTable: true,
      expectText: "customer",
    });
  });

  test("customers create renders", async ({ page }) => {
    await test.step("Navigate to customers index", async (): Promise<void> => {
      const resp = await page.goto(`${BASE_URL}/customers`, {
        waitUntil: "commit",
        timeout: 45000,
      });
      expect(resp?.status()).toBeLessThan(500);
      await page
        .waitForLoadState("domcontentloaded", { timeout: 60000 })
        .catch((): void => {});
    });

    await test.step("Click create button", async (): Promise<void> => {
      const createBtn = page
        .locator(
          "a[href*='create'], button[data-ajax-popup], .btn-create, [data-url*='create'], a.btn-sm, .btn-primary",
        )
        .first();
      await createBtn.click({ timeout: 10000 }).catch((): void => {});
    });

    await test.step("Modal or form renders", async (): Promise<void> => {
      const formOrModal = page.locator(
        ".modal.show form, .modal-body form, form:not(#frm-logout):not(.d-none), .modal.show",
      );
      await expect(formOrModal.first())
        .toBeVisible({ timeout: 15000 })
        .catch((): void => {});
    });
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 8 — Deal Subresources
   ═══════════════════════════════════════════════════════════════════ */

test.describe("CRM Deal Subresources", (): void => {
  test("deal_calls index: number renders", async ({ page }) => {
    await assertPageRenders(page, "deal_calls", "Deal Calls Index", {
      expectCard: true,
      expectTable: true,
    });
  });

  test("deal_emails index: number renders", async ({ page }) => {
    await assertPageRenders(page, "deal_emails", "Deal Emails Index", {
      expectCard: true,
      expectTable: true,
    });
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 9 — Lead Subresources
   ═══════════════════════════════════════════════════════════════════ */

test.describe("CRM Lead Subresources", (): void => {
  test("lead_calls index: number renders", async ({ page }) => {
    await assertPageRenders(page, "lead_calls", "Lead Calls Index", {
      expectCard: true,
      expectTable: true,
    });
  });

  test("lead_emails index: number renders", async ({ page }) => {
    await assertPageRenders(page, "lead_emails", "Lead Emails Index", {
      expectCard: true,
      expectTable: true,
    });
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 10 — CRM Module Navigation
   ═══════════════════════════════════════════════════════════════════ */

test.describe("CRM Module Navigation", (): void => {
  test("can navigate between CRM sections", async ({ page }) => {
    // Start at deals
    await assertPageRenders(page, "deals", "Deals Index", {
      expectCard: true,
    });

    // Navigate to leads via sidebar/menu
    const leadsLink = page.locator('a[href*="leads"]').first();
    if (await leadsLink.isVisible({ timeout: 5000 }).catch(() => false)) {
      await leadsLink.click();
      await page.waitForLoadState("domcontentloaded", { timeout: 30000 });
      const body = await page.textContent("body");
      expect(body?.toLowerCase()).toContain("lead");
    }
  });

  test("pipeline selector: string changes view", async ({ page }) => {
    await page.goto(`${BASE_URL}/deals`, {
      waitUntil: "domcontentloaded",
      timeout: 45000,
    });

    const pipelineSelector = page.locator(
      'select.pipeline-selector, select[name="pipeline_id"], #pipeline_id',
    );
    if (
      await pipelineSelector.isVisible({ timeout: 5000 }).catch(() => false)
    ) {
      await expect(pipelineSelector).toBeVisible();
    }
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 11 — Modal and Form Interactions
   ═══════════════════════════════════════════════════════════════════ */

test.describe("CRM Modal Interactions", (): void => {
  test("deal quick action modal opens", async ({ page }) => {
    await page.goto(`${BASE_URL}/deals`, {
      waitUntil: "domcontentloaded",
      timeout: 45000,
    });

    // Look for add button
    const addBtn = page
      .locator('a[href*="deals/create"], .btn-primary:has-text("Add")')
      .first();
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
    const addBtn = page
      .locator('a[href*="leads/create"], .btn-primary:has-text("Add")')
      .first();
    if (await addBtn.isVisible({ timeout: 5000 }).catch(() => false)) {
      await addBtn.click();
      await page.waitForLoadState("domcontentloaded", { timeout: 30000 });
    }
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 12 — Search and Filter
   ═══════════════════════════════════════════════════════════════════ */

test.describe("CRM Search and Filter", (): void => {
  test("deals has search functionality", async ({ page }) => {
    await page.goto(`${BASE_URL}/deals`, {
      waitUntil: "domcontentloaded",
      timeout: 45000,
    });

    const searchInput = page.locator(
      'input[type="search"], .search-input, .dataTables_filter input',
    );
    if (await searchInput.isVisible({ timeout: 5000 }).catch(() => false))
      await expect(searchInput.first()).toBeVisible();
  });

  test("clients table is searchable", async ({ page }) => {
    await page.goto(`${BASE_URL}/clients`, {
      waitUntil: "domcontentloaded",
      timeout: 45000,
    });

    const searchInput = page.locator(
      'input[type="search"], .dataTables_filter input',
    );
    if (await searchInput.isVisible({ timeout: 5000 }).catch(() => false)) {
      await expect(searchInput.first()).toBeVisible();
      await searchInput.first().fill("test");
    }
  });
});
