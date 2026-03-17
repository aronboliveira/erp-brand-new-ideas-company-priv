import { defineConfig, devices } from "@playwright/test";
import * as path from "node:path";

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
  webServer: {
    command: `node ${path.resolve(__dirname, "mock-server.cjs")}`,
    url: "http://localhost:3847",
    reuseExistingServer: false,
    timeout: 30000,
    stdout: "pipe",
    stderr: "pipe",
  },
});
