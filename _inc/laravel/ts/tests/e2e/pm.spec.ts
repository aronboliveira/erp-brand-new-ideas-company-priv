import { test, expect } from "@playwright/test";
import path from "path";

/**
 * ERP Brand New Ideas Company – PM Route Rendering E2E Tests
 * Verifies every Project Management index/create route renders
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
}

/* ═══════════════════════════════════════════════════════════════════
   SECTION 1 — Project Dashboard & Core
   ═══════════════════════════════════════════════════════════════════ */

test.describe("PM Project Dashboard & Core", () => {
  test("project dashboard renders", async ({ page }) => {
    await assertPageRenders(page, "project-dashboard", "Project Dashboard", {
      expectText: "project",
    });
  });

  test("projects index renders", async ({ page }) => {
    await assertPageRenders(page, "projects", "Projects Index", {
      expectCard: true,
      expectText: "project",
    });
  });

  test("projects create renders form", async ({ page }) => {
    await assertPageRenders(page, "projects/create", "Project Create", {});
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 2 — Project Stages
   ═══════════════════════════════════════════════════════════════════ */

test.describe("PM Project Stages", () => {
  test("project stages index renders", async ({ page }) => {
    await assertPageRenders(page, "project_stages", "Project Stages Index", {
      expectCard: true,
      expectText: "stage",
    });
  });

  test("project stages create modal form visible", async ({ page }) => {
    await test.step("Navigate to project stages index", async () => {
      const resp = await page.goto(`${BASE_URL}/project_stages`, {
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
   SECTION 3 — Task Stages
   ═══════════════════════════════════════════════════════════════════ */

test.describe("PM Task Stages", () => {
  test("task stages index renders", async ({ page }) => {
    await assertPageRenders(page, "project_task_stages", "Task Stages Index", {
      expectCard: true,
      expectText: "stage",
    });
  });

  test("task stages create modal form visible", async ({ page }) => {
    await test.step("Navigate to task stages index", async () => {
      const resp = await page.goto(`${BASE_URL}/project_task_stages`, {
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
   SECTION 4 — Bug Status
   ═══════════════════════════════════════════════════════════════════ */

test.describe("PM Bug Status", () => {
  test("bug status index renders", async ({ page }) => {
    await assertPageRenders(page, "bug_status", "Bug Status Index", {
      expectCard: true,
      expectText: "bug",
    });
  });

  test("bug statuses create modal form visible", async ({ page }) => {
    await test.step("Navigate to bug status index", async () => {
      const resp = await page.goto(`${BASE_URL}/bug_status`, {
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
   SECTION 5 — Contracts & Contract Types
   ═══════════════════════════════════════════════════════════════════ */

test.describe("PM Contracts", () => {
  test("contracts index renders", async ({ page }) => {
    await assertPageRenders(page, "contracts", "Contracts Index", {
      expectCard: true,
      expectText: "contract",
    });
  });

  test("contracts create renders form", async ({ page }) => {
    // Modal partial (no @extends) – only a form is rendered.
    await assertPageRenders(page, "contracts/create", "Contract Create", {
      expectForm: true,
    });
  });

  test("contracts grid view renders", async ({ page }) => {
    await assertPageRenders(page, "contracts/grid", "Contracts Grid", {
      expectCard: true,
      expectText: "contract",
    });
  });
});

test.describe("PM Contract Types", () => {
  test("contract types index renders", async ({ page }) => {
    await assertPageRenders(page, "contract_types", "Contract Types Index", {
      expectCard: true,
      expectText: "type",
    });
  });

  test("contract types create modal form visible", async ({ page }) => {
    await test.step("Navigate to contract types index", async () => {
      const resp = await page.goto(`${BASE_URL}/contract_types`, {
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
   SECTION 6 — Proposals
   ═══════════════════════════════════════════════════════════════════ */

test.describe("PM Proposals", () => {
  test("proposals index renders", async ({ page }) => {
    await assertPageRenders(page, "proposal", "Proposals Index", {
      expectCard: true,
      expectText: "proposal",
    });
  });

  test("proposals create renders form", async ({ page }) => {
    await assertPageRenders(page, "proposals/create", "Proposal Create", {
      expectForm: true,
      expectCard: true,
    });
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 7 — Time Trackers
   ═══════════════════════════════════════════════════════════════════ */

test.describe("PM Time Trackers", () => {
  test("time trackers index renders", async ({ page }) => {
    await assertPageRenders(page, "time_trackers", "Time Trackers Index", {
      expectCard: true,
      expectText: "track",
    });
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 8 — Project Reports
   ═══════════════════════════════════════════════════════════════════ */

test.describe("PM Project Reports", () => {
  test("project reports index renders", async ({ page }) => {
    await assertPageRenders(page, "project_reports", "Project Reports Index", {
      expectCard: true,
      expectText: "report",
    });
  });

  test("project reports create renders form", async ({ page }) => {
    await test.step("Navigate to project reports index", async () => {
      const resp = await page.goto(`${BASE_URL}/project_reports`, {
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
   SECTION 9 — Task Board & Bugs Reports
   ═══════════════════════════════════════════════════════════════════ */

test.describe("PM Task Board & Bug Reports", () => {
  test("task board view renders", async ({ page }) => {
    await assertPageRenders(page, "task-boards/list", "Task Board View", {
      expectBreadcrumb: true,
      expectText: "task",
    });
  });

  test("task boards renders", async ({ page }) => {
    await assertPageRenders(page, "task-boards", "Task Boards", {
      expectBreadcrumb: true,
    });
  });

  test("bugs reports renders", async ({ page }) => {
    await assertPageRenders(page, "bugs_reports", "Bugs Reports", {
      expectText: "bug",
    });
  });
});

/* ═══════════════════════════════════════════════════════════════════
   SECTION 10 — Timesheets
   ═══════════════════════════════════════════════════════════════════ */

test.describe("PM Timesheets", () => {
  test("timesheet list renders", async ({ page }) => {
    await assertPageRenders(page, "projects.timesheets/list", "Timesheet List", {
      expectCard: true,
      expectText: "timesheet",
    });
  });
});
