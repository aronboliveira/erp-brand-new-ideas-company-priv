/**
 * @file mock-routes-e2e.spec.ts
 * @description Playwright E2E spec that loads each mock route page
 * and validates the route table structure, filter, and fetch controls.
 *
 * These tests load the static HTML files directly via file:// protocol,
 * so no running server is required.
 *
 * Generated: 2025-02-28
 */
import { test, expect } from "@playwright/test";
import path from "path";
import fs from "fs";
import { fileURLToPath } from "url";

const CURRENT_DIR = path.dirname(fileURLToPath(import.meta.url));
const MOCKS_DIR = path.resolve(CURRENT_DIR, "..", "pages", "mocks");

/** Get list of category HTML files */
function getCategoryFiles(): string[] {
  if (!fs.existsSync(MOCKS_DIR)) return [];
  return fs
    .readdirSync(MOCKS_DIR)
    .filter(f => f.endsWith(".html") && f !== "index.html")
    .sort();
}

const categoryFiles = getCategoryFiles();

test.describe("Mock Route Pages — Index", () => {
  test("index page loads and lists categories", async ({ page }) => {
    const indexFile = path.join(MOCKS_DIR, "index.html");
    await page.goto(`file://${indexFile}`);
    await expect(page.locator("h1")).toContainText("Route Index");
    await expect(page.locator(".timestamp")).toContainText("Generated:");
    // Should have at least 15 category links
    const links = page.locator("table tbody tr");
    await expect(links).toHaveCount(await links.count());
    expect(await links.count()).toBeGreaterThanOrEqual(15);
  });
});

for (const filename of categoryFiles) {
  test.describe(`Mock Route Page — ${filename.replace(".html", "")}`, () => {
    test("loads with correct structure", async ({ page }) => {
      const filePath = path.join(MOCKS_DIR, filename);
      await page.goto(`file://${filePath}`);

      // Title
      await expect(page).toHaveTitle(/Mock Test Page/);

      // Timestamp
      await expect(page.locator(".timestamp")).toContainText("Generated:");

      // Route table
      const table = page.locator("#route-table");
      await expect(table).toBeVisible();

      // At least one route row
      const rows = table.locator("tbody tr");
      expect(await rows.count()).toBeGreaterThan(0);

      // Headers
      const headers = table.locator("thead th");
      const headerTexts = await headers.allTextContents();
      const lowerHeaders = headerTexts.map(h => h.trim().toLowerCase());
      expect(lowerHeaders).toContain("method");
      expect(lowerHeaders).toContain("uri");
    });

    test("filter input works", async ({ page }) => {
      const filePath = path.join(MOCKS_DIR, filename);
      await page.goto(`file://${filePath}`);

      const filter = page.locator("#filter");
      await expect(filter).toBeVisible();

      const totalRows = await page.locator("#route-table tbody tr").count();

      // Type a very specific filter that should hide most rows
      await filter.fill("ZZZZZ_NONEXISTENT_ROUTE");
      await page.waitForTimeout(100);

      // Count visible rows
      const visibleRows = await page
        .locator('#route-table tbody tr:not([style*="display: none"])')
        .count();
      expect(visibleRows).toBeLessThan(totalRows);

      // Clear filter — all rows should reappear
      await filter.fill("");
      await page.waitForTimeout(100);
      const restoredRows = await page
        .locator('#route-table tbody tr:not([style*="display: none"])')
        .count();
      expect(restoredRows).toBe(totalRows);
    });

    test("bulk controls exist", async ({ page }) => {
      const filePath = path.join(MOCKS_DIR, filename);
      await page.goto(`file://${filePath}`);

      await expect(page.locator("#btn-fetch-all")).toBeVisible();
      await expect(page.locator("#btn-clear")).toBeVisible();
    });

    test("fetch button exists for each route", async ({ page }) => {
      const filePath = path.join(MOCKS_DIR, filename);
      await page.goto(`file://${filePath}`);

      const rows = await page.locator("#route-table tbody tr").count();
      const buttons = await page.locator(".btn-fetch").count();
      expect(buttons).toBe(rows);
    });

    test("each route has auth badge", async ({ page }) => {
      const filePath = path.join(MOCKS_DIR, filename);
      await page.goto(`file://${filePath}`);

      const badges = page.locator(".badge");
      const count = await badges.count();
      expect(count).toBeGreaterThan(0);

      for (let i = 0; i < count; i++) {
        const text = await badges.nth(i).textContent();
        expect(["Auth", "Public"]).toContain(text?.trim());
      }
    });
  });
}
