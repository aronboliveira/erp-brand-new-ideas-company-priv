// @ts-check
const { test: setup, expect } = require("@playwright/test");
const fs = require("fs");
const path = require("path");

const STORAGE_STATE = path.join(__dirname, ".auth/user.json");

setup("authenticate", async ({ page, context }) => {
  console.log("Starting authentication setup...");

  try {
    console.log("Navigating to login page...");
    await page.goto("/login", { timeout: 30000 });

    console.log("Waiting for login form...");
    await page.waitForSelector("#email-input", { timeout: 10000 });

    console.log("Filling credentials...");
    await page.fill(
      "#email-input",
      "u_1ecb6d5a-e2c5-4961-af3b-0ad83f9d259c@test.local",
    );
    await page.fill("#pw-input", "Admin@1234");

    console.log("Clicking login button...");
    await page.click("#saveBtn");

    console.log("Waiting for redirect...");
    await page.waitForURL(/.*(?!login).*$/, { timeout: 30000 });

    const finalUrl = page.url();
    console.log("Redirected to:", finalUrl);

    expect(finalUrl).not.toContain("login");

    // Ensure auth directory exists
    const authDir = path.dirname(STORAGE_STATE);
    if (!fs.existsSync(authDir)) {
      fs.mkdirSync(authDir, { recursive: true });
    }

    // Save storage state
    await context.storageState({ path: STORAGE_STATE });
    console.log("Authentication state saved to:", STORAGE_STATE);
  } catch (error) {
    console.error("Authentication setup failed:", error.message);
    await page.screenshot({ path: "auth-error.png" });
    throw error;
  }
});
