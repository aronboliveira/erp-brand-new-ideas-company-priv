/// <reference types="node" />
// @ts-check
/* eslint-env node */
/* global process */
const { defineConfig, devices } = require("@playwright/test");

/**
 * Playwright Configuration for Frontend Mock Page Tests
 *
 * This configuration is specifically designed for testing mock pages
 * that simulate RBAC, API responses, forms, and navigation scenarios
 * without requiring a live backend server.
 *
 * Run with: npx playwright test --config=playwright-frontend.config.cjs
 *
 * @see https://playwright.dev/docs/test-configuration
 */
module.exports = defineConfig({
  testDir: "./tests/frontend/js/e2e",

  /* Run tests in files in parallel */
  fullyParallel: true,

  /* Fail the build on CI if you accidentally left test.only in the source code. */
  forbidOnly: !!process.env.CI,

  /* Retry on CI only */
  retries: process.env.CI ? 2 : 1,

  /* Parallel workers */
  workers: process.env.CI ? 1 : 4,

  /* Reporter to use */
  reporter: [["html", { open: "never", outputFolder: "tests/frontend/js/playwright-report" }], ["json", { outputFile: "tests/frontend/js/test-results.json" }], ["list"]],

  /* Shared settings for all the projects below */
  use: {
    /* No baseURL needed - we use file:// protocol for mock pages */

    /* Collect trace when retrying the failed test */
    trace: "on-first-retry",

    /* Screenshot on failure */
    screenshot: "only-on-failure",

    /* Video on failure */
    video: "retain-on-failure",

    /* Viewport size */
    viewport: { width: 1280, height: 720 },
  },

  /* Configure projects for major browsers */
  projects: [
    {
      name: "chromium",
      use: { ...devices["Desktop Chrome"] },
    },
    {
      name: "firefox",
      use: { ...devices["Desktop Firefox"] },
    },
    {
      name: "webkit",
      use: { ...devices["Desktop Safari"] },
    },
    /* Mobile viewports */
    {
      name: "Mobile Chrome",
      use: { ...devices["Pixel 5"] },
    },
    {
      name: "Mobile Safari",
      use: { ...devices["iPhone 12"] },
    },
  ],

  /* Timeout settings — 45s base; render-timing tests call test.slow() to triple it */
  timeout: 45000,
  expect: {
    timeout: 10000,
  },

  /* Web server for serving mock pages with ES module support */
  webServer: {
    command: "npx http-server tests/frontend/js/pages -p 3847 -c-1",
    url: "http://localhost:3847",
    reuseExistingServer: false,
    timeout: 10000,
  },

  /* Output directory for test artifacts */
  outputDir: "tests/frontend/js/test-results",
});
