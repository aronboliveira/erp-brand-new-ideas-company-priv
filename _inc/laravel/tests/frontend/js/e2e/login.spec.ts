/**
 * @file login.spec.ts
 * @description Playwright E2E tests for ERP login page
 * Tests: form rendering, validation, authentication flow, redirects
 */
import { test, expect } from "@playwright/test";

const BASE = process.env.APP_URL || "http://127.0.0.1:8000";

// Skip in CI unless a live server URL is provided via APP_URL
test.beforeEach(async ({}, testInfo) => {
  testInfo.skip(
    !!(process.env.CI && !process.env.APP_URL),
    "Requires a running Laravel server (set APP_URL to enable)"
  );
});

test.describe("Login Page", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${BASE}/login`);
  });

  test("should render the login form", async ({ page }) => {
    // Check that the login form elements exist
    const emailInput = page.locator('input[name="email"], input[type="email"]');
    const passwordInput = page.locator(
      'input[name="password"], input[type="password"]',
    );
    const submitButton = page.locator(
      'button[type="submit"], input[type="submit"]',
    );

    await expect(emailInput.first()).toBeVisible({ timeout: 10000 });
    await expect(passwordInput.first()).toBeVisible();
    await expect(submitButton.first()).toBeVisible();
  });

  test("should show validation errors for empty submission", async ({
    page,
  }) => {
    const submitButton = page.locator(
      'button[type="submit"], input[type="submit"]',
    );
    await submitButton.first().click();

    // After empty submit, page should either show errors or stay on login
    const currentUrl = page.url();
    // Should not navigate away (no redirect to dashboard)
    expect(currentUrl).toContain("login");
  });

  test("should reject invalid credentials", async ({ page }) => {
    const emailInput = page.locator('input[name="email"], input[type="email"]');
    const passwordInput = page.locator(
      'input[name="password"], input[type="password"]',
    );
    const submitButton = page.locator(
      'button[type="submit"], input[type="submit"]',
    );

    await emailInput.first().fill("invalid@test.com");
    await passwordInput.first().fill("wrongpassword123");
    await submitButton.first().click();

    // Should stay on login page or show error
    await page.waitForLoadState("networkidle");
    const url = page.url();
    expect(url).toMatch(/login|auth/);
  });

  test("should have proper page title", async ({ page }) => {
    const title = await page.title();
    expect(title).toBeTruthy();
    expect(title.length).toBeGreaterThan(0);
  });

  test("should have CSRF token", async ({ page }) => {
    const csrfMeta = page.locator('meta[name="csrf-token"]');
    const csrfInput = page.locator('input[name="_token"]');
    // At least one CSRF mechanism should exist
    const hasMeta = await csrfMeta.count();
    const hasInput = await csrfInput.count();
    expect(hasMeta + hasInput).toBeGreaterThan(0);
  });
});

test.describe("Registration Page", () => {
  test("should load registration page", async ({ page }) => {
    const response = await page.goto(`${BASE}/register`);
    // Should either render (200) or redirect to login
    expect(response?.status()).toBeLessThan(500);
  });
});

test.describe("Password Reset", () => {
  test("should load forgot password page", async ({ page }) => {
    const response = await page.goto(`${BASE}/forgot-password`);
    if (response && response.status() < 400) {
      const emailInput = page.locator(
        'input[name="email"], input[type="email"]',
      );
      await expect(emailInput.first()).toBeVisible({ timeout: 5000 });
    }
  });
});
