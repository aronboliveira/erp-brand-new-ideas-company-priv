/**
 * Playwright config for integration tests.
 *
 * Launches the mock API server before tests and tears it down after.
 *
 * Usage:
 *   npx playwright test --config=tests/integration/playwright-integration.config.ts
 */

import { defineConfig } from "@playwright/test";

export default defineConfig({
  testDir: ".",
  testMatch: "**/*.spec.ts",
  timeout: 15_000,
  retries: 0,
  workers: 1,

  use: {
    baseURL: "http://localhost:3334",
  },

  webServer: {
    command: "node tests/integration/mock-api-server.cjs 3334",
    port: 3334,
    reuseExistingServer: !process.env.CI,
    timeout: 10_000,
    cwd: process.cwd().endsWith("/ts") ? process.cwd() : `${process.cwd()}/_inc/laravel/ts`,
  },
});
