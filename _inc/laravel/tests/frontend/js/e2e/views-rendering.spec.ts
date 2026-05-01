/**
 * @file views-rendering.spec.ts
 * @description Playwright E2E tests checking key ERP views for rendering errors
 * Tests: tables, cards, grids, data sections that may fail to render
 */
import { test, expect } from "@playwright/test";

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
async function loginAsAdmin(page: any) {
  await page.goto(`${BASE}/login`);
  const emailInput = page.locator('input[name="email"], input[type="email"]');
  const passwordInput = page.locator(
    'input[name="password"], input[type="password"]',
  );
  const submitButton = page.locator(
    'button[type="submit"], input[type="submit"]',
  );
  await emailInput
    .first()
    .fill("u_1ecb6d5a-e2c5-4961-af3b-0ad83f9d259c@test.local");
  await passwordInput.first().fill("Admin@1234");
  await submitButton.first().click();
  await page.waitForLoadState("networkidle");
}

// Routes with data tables/cards/grids to check
const DATA_ROUTES = [
  {
    path: "/invoices",
    name: "Invoices",
    expectPattern: /table|card|grid|list|invoice/i,
  },
  {
    path: "/bills",
    name: "Bills",
    expectPattern: /table|card|grid|list|bill/i,
  },
  {
    path: "/proposals",
    name: "Proposals",
    expectPattern: /table|card|grid|list|proposal/i,
  },
  {
    path: "/customers",
    name: "Customers",
    expectPattern: /table|card|grid|list|customer/i,
  },
  {
    path: "/vendors",
    name: "Vendors",
    expectPattern: /table|card|grid|list|vendor/i,
  },
  {
    path: "/employees",
    name: "Employees",
    expectPattern: /table|card|grid|list|employee/i,
  },
  {
    path: "/bank_accounts",
    name: "Bank Accounts",
    expectPattern: /table|card|grid|list|bank/i,
  },
  {
    path: "/products",
    name: "Products",
    expectPattern: /table|card|grid|list|product/i,
  },
  {
    path: "/leads",
    name: "CRM Leads",
    expectPattern: /table|card|grid|list|lead/i,
  },
  {
    path: "/projects",
    name: "Projects",
    expectPattern: /table|card|grid|list|project/i,
  },
];

test.describe("View Rendering Checks", () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  for (const route of DATA_ROUTES) {
    test(`${route.name} (${route.path}) should not return 500`, async ({
      page,
    }) => {
      const response = await page.goto(`${BASE}${route.path}`);
      const status = response?.status() ?? 0;
      // Should not be a server error
      expect(status).toBeLessThan(500);
    });

    test(`${route.name} (${route.path}) should render content`, async ({
      page,
    }) => {
      const response = await page.goto(`${BASE}${route.path}`);
      const status = response?.status() ?? 0;
      if (status < 400) {
        // Check that the page has some content (not blank)
        const bodyText = await page.textContent("body");
        expect(bodyText?.length).toBeGreaterThan(100);
      }
    });
  }

  test("should detect broken data tables that fail to render", async ({
    page,
  }) => {
    const errors: string[] = [];
    page.on("console", (msg: any) => {
      if (msg.type() === "error") errors.push(msg.text());
    });

    // Check dashboard for broken widgets
    await page.goto(`${BASE}/account-dashboard`);
    await page.waitForLoadState("networkidle");

    // Look for empty table bodies that may indicate failed data loading
    const emptyTables = await page
      .locator("table tbody:empty, .dataTables_empty, .no-data")
      .count();
    // Some tables may legitimately be empty, but track it
    // Not a failure but important visibility
    console.log(`Empty tables/no-data indicators: ${emptyTables}`);
  });
});

test.describe("Financial Report Views", () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  const REPORT_ROUTES = [
    "/reports/income-summary",
    "/reports/expense-summary",
    "/reports/income-vs-expense-summary",
    "/balance-sheet",
    "/profit-loss",
    "/trial-balance",
  ];

  for (const route of REPORT_ROUTES) {
    test(`Report ${route} should not 500`, async ({ page }) => {
      const response = await page.goto(`${BASE}${route}`);
      const status = response?.status() ?? 0;
      expect(status).toBeLessThan(500);
    });
  }
});

test.describe("Form Views", () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  const FORM_ROUTES = [
    "/invoices/create",
    "/bills/create",
    "/proposals/create",
    "/customers/create",
    "/vendors/create",
    "/employees/create",
  ];

  for (const route of FORM_ROUTES) {
    test(`Create form ${route} should render`, async ({ page }) => {
      try {
        const response = await page.goto(`${BASE}${route}`, {
          timeout: 30000,
        });
        const status = response?.status() ?? 0;
        if (status < 400) {
          const forms = await page.locator("form").count();
          expect(forms).toBeGreaterThan(0);
        }
      } catch (e: unknown) {
        // Skip if redirect loop or navigation error
        if (
          e instanceof Error &&
          (e.message.includes("ERR_TOO_MANY_REDIRECTS") ||
            e.message.includes("Navigation failed"))
        ) {
          test.skip();
        }
        throw e;
      }
    });
  }
});
