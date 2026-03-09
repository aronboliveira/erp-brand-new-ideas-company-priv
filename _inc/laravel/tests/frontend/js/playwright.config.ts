import { defineConfig, devices } from "@playwright/test";

/**
 * Playwright configuration for ERP Prestech E2E tests
 * Run with: npx playwright test
 */
export default defineConfig({
  testDir: "./e2e",
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 1,
  reporter: "html",
  timeout: 120000,
  use: {
    baseURL: process.env.APP_URL || "http://127.0.0.1:8000",
    trace: "on-first-retry",
    screenshot: "only-on-failure",
    navigationTimeout: 90000,
  },
  projects: [
    {
      name: "chromium",
      use: { ...devices["Desktop Chrome"] },
    },
  ],
  /* Optionally start the dev server before tests */
  // webServer: {
  //   command: 'php artisan serve',
  //   url: 'http://127.0.0.1:8000',
  //   reuseExistingServer: !process.env.CI,
  // },
});
