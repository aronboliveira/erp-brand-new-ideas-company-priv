// @ts-check
const { chromium } = require("@playwright/test");
const fs = require("fs");
const path = require("path");

const BASE_URL = process.env.BASE_URL || "http://localhost:8000";
const STORAGE_STATE = path.join(__dirname, ".auth/user.json");

async function globalSetup() {
  console.log("Starting authentication setup...");

  const browser = await chromium.launch();
  const context = await browser.newContext({
    viewport: { width: 1280, height: 720 },
  });
  const page = await context.newPage();

  try {
    console.log("Navigating to login page...");
    await page.goto(`${BASE_URL}/login`, {
      timeout: 120000,
      waitUntil: "networkidle",
    });

    console.log("Waiting for login form...");
    // Use visible locator to avoid duplicate-ID collisions (responsive layout)
    const emailInput = page.locator("#email-input").locator("visible=true").first();
    const pwInput = page.locator("#pw-input").locator("visible=true").first();
    const submitBtn = page.locator("#saveBtn").locator("visible=true").first();

    await emailInput.waitFor({ state: "visible", timeout: 10000 });

    console.log("Filling credentials...");
    await emailInput.fill("u_68ca0ef2-8cf2-4930-9129-24da61a4874a@test.local");
    await pwInput.fill("password");

    console.log("Clicking login button...");
    // Wait for navigation together with click to avoid race conditions
    await Promise.all([
      page.waitForURL(url => !url.pathname.endsWith("/login"), {
        timeout: 120000,
      }),
      submitBtn.click(),
    ]);

    const finalUrl = page.url();
    console.log("Redirected to:", finalUrl);

    if (new URL(finalUrl).pathname.endsWith("/login")) {
      throw new Error("Login failed - still on login page");
    }

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
    const screenshotPath = path.join(__dirname, "auth-error.png");
    await page.screenshot({ path: screenshotPath, fullPage: true });
    console.error("Screenshot saved to:", screenshotPath);
    throw error;
  } finally {
    await browser.close();
  }
}

globalSetup()
  .then(() => {
    console.log("Auth setup completed successfully");
    process.exit(0);
  })
  .catch(error => {
    console.error("Auth setup failed:", error);
    process.exit(1);
  });
