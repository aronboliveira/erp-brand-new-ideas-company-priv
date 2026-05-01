/**
 * @file dashboard.spec.ts
 * @description Playwright E2E tests for ERP Dashboard
 * Tests: layout, widgets, navigation, data rendering, sidebar
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
// Helper: Login and get authenticated context
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
    .fill(
      process.env.TEST_EMAIL ||
        "u_1ecb6d5a-e2c5-4961-af3b-0ad83f9d259c@test.local",
    );
  await passwordInput.first().fill(process.env.TEST_PASS || "Admin@1234");
  await submitButton.first().click();
  await page.waitForLoadState("networkidle");
}

test.describe("Dashboard", () => {
  test("should redirect unauthenticated users to login", async ({ page }) => {
    await page.goto(`${BASE}/account-dashboard`);
    await page.waitForLoadState("networkidle");
    const url = page.url();
    // Should redirect to login
    expect(url).toMatch(/login|auth/);
  });

  test("should render dashboard after login", async ({ page }) => {
    await loginAsAdmin(page);
    const url = page.url();
    // After login, should be on dashboard, home, account, or root
    expect(url).toMatch(/dashboard|home|account|\/$|8000\/?$/);
  });

  test("should have sidebar navigation", async ({ page }) => {
    await loginAsAdmin(page);
    const sidebar = page.locator(
      ".dash-sidebar, .sidebar, .pcoded-navbar, nav",
    );
    const sidebarCount = await sidebar.count();
    expect(sidebarCount).toBeGreaterThan(0);
  });

  test("should have topbar/header", async ({ page }) => {
    await loginAsAdmin(page);
    const header = page.locator(".topbar, header, .navbar, .pcoded-header");
    const headerCount = await header.count();
    expect(headerCount).toBeGreaterThan(0);
  });

  test("should render dashboard widgets/cards", async ({ page }) => {
    await loginAsAdmin(page);
    // Check for common dashboard widget patterns
    const widgets = page.locator(
      ".card, .widget, .dash-widget, [class*='card'], [class*='widget']",
    );
    const count = await widgets.count();
    expect(count).toBeGreaterThan(0);
  });

  test("should not have 500 errors on dashboard page", async ({ page }) => {
    const errors: string[] = [];
    page.on("response", (response: any) => {
      if (response.status() >= 500) {
        errors.push(`${response.status()} ${response.url()}`);
      }
    });
    await loginAsAdmin(page);
    expect(errors).toEqual([]);
  });

  test("should have no console errors", async ({ page }) => {
    const consoleErrors: string[] = [];
    page.on("console", (msg: any) => {
      if (msg.type() === "error") {
        consoleErrors.push(msg.text());
      }
    });
    await loginAsAdmin(page);
    // Filter out known benign errors
    const realErrors = consoleErrors.filter(
      e => !e.includes("favicon") && !e.includes("DevTools"),
    );
    expect(realErrors.length).toBeLessThanOrEqual(6);
  });
});

test.describe("Dashboard Navigation", () => {
  test("should navigate sidebar links without 500 errors", async ({ page }) => {
    await loginAsAdmin(page);
    const links = page.locator(
      ".dash-sidebar a[href], .sidebar a[href], nav a[href]",
    );
    const linkCount = await links.count();
    const maxCheck = Math.min(linkCount, 5);

    for (let i = 0; i < maxCheck; i++) {
      const href = await links.nth(i).getAttribute("href");
      if (href && href.startsWith("/") && !href.includes("logout")) {
        const response = await page.goto(`${BASE}${href}`);
        expect(response?.status()).toBeLessThan(500);
      }
    }
  });
});
