// @ts-check
import { defineConfig, devices } from "@playwright/test";

/**
 * Playwright Configuration for TypeScript Build Harness Tests
 *
 * Tests the compiled TypeScript output (ts/dist/) against mock HTML pages
 * that mirror production Blade templates.
 *
 * Usage:
 *   # Start harness server first
 *   cd ts/tests && node serve-harness.cjs &
 *
 *   # Run tests
 *   cd ts && npx playwright test --config=playwright.harness.config.ts
 */
export default defineConfig({
  testDir: "./tests/harness-specs",

  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,

  reporter: [
    ["html", { open: "never", outputFolder: "tests/harness-report" }],
    ["list"],
  ],

  use: {
    baseURL: "http://localhost:3333",
    trace: "on-first-retry",
    screenshot: "only-on-failure",
  },

  projects: [
    {
      name: "chromium",
      use: { ...devices["Desktop Chrome"] },
    },
    {
      name: "firefox",
      use: { ...devices["Desktop Firefox"] },
    },
  ],

  /* Start harness server before tests */
  webServer: {
    command: "node tests/serve-harness.cjs 3333",
    url: "http://localhost:3333/harness/",
    reuseExistingServer: !process.env.CI,
    timeout: 10000,
  },
});
