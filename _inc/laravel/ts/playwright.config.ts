// @ts-check
import { defineConfig, devices } from "@playwright/test";

/**
 * Playwright Configuration for TypeScript Migration Tests
 *
 * This configuration is for testing the TypeScript versions of
 * the converted JavaScript tests.
 *
 * Run with: npx playwright test --config=playwright.config.ts
 *
 * @see https://playwright.dev/docs/test-configuration
 */
export default defineConfig({
  testDir: "./src/tests/e2e",

  /* Run tests in files in parallel */
  fullyParallel: false,

  /* Fail the build on CI if you accidentally left test.only in the source code. */
  forbidOnly: !!process.env.CI,

  /* Retry on CI only */
  retries: process.env.CI ? 2 : 0,

  /* Opt out of parallel tests on CI. */
  workers: process.env.CI ? 1 : undefined,

  /* Reporter to use. See https://playwright.dev/docs/test-reporters */
  reporter: [
    ["html", { open: "never", outputFolder: "playwright-report" }],
    ["json", { outputFile: "test-results.json" }],
  ],

  /* Shared settings for all the projects below */
  use: {
    /* Base URL to use in actions like `await page.goto('/')`. */
    baseURL: process.env.APP_URL || "http://localhost:8000",

    /* Collect trace when retrying the failed test. */
    trace: "on-first-retry",

    /* Screenshot on failure */
    screenshot: "only-on-failure",

    /* Video on failure */
    video: "retain-on-failure",
  },

  /* Configure projects for major browsers */
  projects: [
    /* Auth setup — runs before all other projects */
    {
      name: "setup",
      testMatch: /auth\.setup\.ts/,
    },
    {
      name: "chromium",
      use: {
        ...devices["Desktop Chrome"],
        storageState: "src/tests/e2e/.auth/user.json",
      },
      dependencies: ["setup"],
    },
  ],

  /* Timeout settings */
  timeout: 60000,
  expect: {
    timeout: 10000,
  },

  /* Output directory for test artifacts */
  outputDir: "test-results",
});
