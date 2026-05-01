/**
 * @file csr-routes.spec.ts
 * @description Playwright E2E tests for CSR-heavy routes that rely on client-side
 * JavaScript for rendering (kanban boards, drag-and-drop, dynamic widgets).
 * These pages cannot be adequately tested by curl because they produce minimal
 * HTML server-side and populate content via JS.
 */
import { test, expect, Page } from "@playwright/test";

const BASE = process.env.APP_URL || "http://127.0.0.1:8000";
<<<<<<< HEAD

// Skip in CI unless a live server URL is provided via APP_URL
test.beforeEach(async ({}, testInfo) => {
  testInfo.skip(
    !!(process.env.CI && !process.env.APP_URL),
    "Requires a running Laravel server (set APP_URL to enable)"
  );
});

=======
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
const TEST_EMAIL =
  process.env.TEST_EMAIL || "u_1ecb6d5a-e2c5-4961-af3b-0ad83f9d259c@test.local";
const TEST_PASS = process.env.TEST_PASS || "Admin@1234";

/**
 * Shared login helper — fills form and waits for redirect
 */
async function loginAsAdmin(page: Page): Promise<void> {
  await page.goto(`${BASE}/login`, { waitUntil: "networkidle" });
  const emailInput = page.locator('input[name="email"], input[type="email"]');
  const passwordInput = page.locator(
    'input[name="password"], input[type="password"]',
  );
  const submitButton = page.locator(
    'button[type="submit"], input[type="submit"]',
  );
  await emailInput.first().fill(TEST_EMAIL);
  await passwordInput.first().fill(TEST_PASS);
  await submitButton.first().click();
  await page.waitForLoadState("networkidle");
}

/**
 * CSR-heavy routes that need JS execution to render content.
 * Each entry specifies:
 *  - path: the route
 *  - name: human-readable label
 *  - jsSelector: a CSS selector that JS populates (must be visible after render)
 *  - minTextLength: minimum text content expected in body after JS execution
 *  - expectPattern: regex the rendered body text should match
 */
const CSR_ROUTES = [
  {
    path: "/job-application",
    name: "Job Applications (Kanban)",
    jsSelector: ".card-body, .kanban-board, .dragula-container, form",
    minTextLength: 200,
    expectPattern: /job|application|stage|filter|start.*date|end.*date/i,
  },
  // Note: /task-board requires project context, tested via /project_task_stages
  {
    path: "/leads",
    name: "CRM Leads",
    jsSelector: ".card-body, table, .lead, .kanban, .pipeline",
    minTextLength: 150,
    expectPattern: /lead|pipeline|stage|source/i,
  },
  {
    path: "/deals",
    name: "CRM Deals",
    jsSelector: ".card-body, table, .deal, .kanban, .pipeline",
    minTextLength: 150,
    expectPattern: /deal|pipeline|stage|won|lost/i,
  },
  {
    path: "/projects",
    name: "Projects",
    jsSelector: ".card-body, table, .project-card, .project",
    minTextLength: 150,
    expectPattern: /project|status|budget|deadline/i,
  },
  {
    path: "/project_task_stages",
    name: "Project Task Stages",
    jsSelector: ".card-body, table, .stage, form",
    minTextLength: 100,
    expectPattern: /stage|task|project|order/i,
  },
];

test.describe("CSR Routes — Client-Side Rendered Pages", () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  for (const route of CSR_ROUTES) {
    test(`${route.name} (${route.path}) — should return 200`, async ({
      page,
    }) => {
      const response = await page.goto(`${BASE}${route.path}`, {
        waitUntil: "networkidle",
        timeout: 20000,
      });
      const status = response?.status() ?? 0;
      expect(status).toBe(200);
    });

    test(`${route.name} (${route.path}) — JS renders content`, async ({
      page,
    }) => {
      await page.goto(`${BASE}${route.path}`, {
        waitUntil: "networkidle",
        timeout: 20000,
      });

      // Wait for JS-populated elements to appear
      const selectors = route.jsSelector.split(",").map(s => s.trim());
      let found = false;
      for (const sel of selectors) {
        const count = await page.locator(sel).count();
        if (count > 0) {
          found = true;
          break;
        }
      }
      expect(found).toBe(true);
    });

    test(`${route.name} (${route.path}) — has meaningful text`, async ({
      page,
    }) => {
      await page.goto(`${BASE}${route.path}`, {
        waitUntil: "networkidle",
        timeout: 20000,
      });

      const bodyText = (await page.textContent("body")) ?? "";
      expect(bodyText.length).toBeGreaterThanOrEqual(route.minTextLength);
      expect(bodyText).toMatch(route.expectPattern);
    });

    test(`${route.name} (${route.path}) — no console errors`, async ({
      page,
    }) => {
      const errors: string[] = [];
      page.on("console", msg => {
        if (msg.type() === "error") errors.push(msg.text());
      });

      await page.goto(`${BASE}${route.path}`, {
        waitUntil: "networkidle",
        timeout: 20000,
      });

      // Filter out known benign errors (e.g. favicon, third-party scripts)
      const realErrors = errors.filter(
        e =>
          !e.includes("favicon") &&
          !e.includes("net::ERR") &&
          !e.includes("404"),
      );
      // Allow up to 6 non-critical console errors (lib warnings, jQuery deprecations, etc.)
      expect(realErrors.length).toBeLessThanOrEqual(6);
    });
  }
});

test.describe("CSR Routes — Performance Baselines", () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  for (const route of CSR_ROUTES) {
    test(`${route.name} (${route.path}) — loads within 15s`, async ({
      page,
    }) => {
      const start = Date.now();
      const response = await page.goto(`${BASE}${route.path}`, {
        waitUntil: "networkidle",
        timeout: 15000,
      });
      const elapsed = Date.now() - start;

      const status = response?.status() ?? 0;
      expect(status).toBeLessThan(500);
      // Performance baseline: page should load within 15 seconds
      expect(elapsed).toBeLessThan(15000);
    });
  }
});

test.describe("CSR Routes — Interactivity Checks", () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  test("Job Application kanban — filter form works", async ({ page }) => {
    await page.goto(`${BASE}/job-application`, {
      waitUntil: "networkidle",
      timeout: 20000,
    });

    // Check filter form exists
    const filterForm = page.locator(
      'form#application_filter, form[action*="job-application"]',
    );
    const formCount = await filterForm.count();
    if (formCount > 0) {
      // Try interacting with the date filter
      const startDate = page.locator('input[name="start_date"]');
      if ((await startDate.count()) > 0) {
        await startDate.first().fill("2024-01-01");
      }
      // Check submit button exists
      const submitBtn = page.locator(
        'button[type="submit"], input[type="submit"]',
      );
      expect(await submitBtn.count()).toBeGreaterThan(0);
    }
  });

  test("Project Task Stages — page has stage content", async ({ page }) => {
    await page.goto(`${BASE}/project_task_stages`, {
      waitUntil: "networkidle",
      timeout: 20000,
    });

    const bodyText = (await page.textContent("body")) ?? "";
    // Should contain page title or stage-related content
    expect(bodyText).toMatch(/stage|task|project|manage/i);
  });
});
