import { test, expect, type ConsoleMessage, type Page } from "@playwright/test";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const CURRENT_DIR = path.dirname(fileURLToPath(import.meta.url));
const MOCKS_ROOT = path.resolve(CURRENT_DIR, "..", "pages", "mocks");
const RBAC_ROOT = path.join(MOCKS_ROOT, "rbac");
const ROLE_PAGES = [
  "super-admin.html",
  "admin.html",
  "hr.html",
  "accountant.html",
  "client.html",
  "guest.html",
];

function fileUrl(file: string): string {
  return `file://${file}`;
}

function getMockHtmlFiles(dir = MOCKS_ROOT): string[] {
  const entries = fs.readdirSync(dir, { withFileTypes: true });
  const files: string[] = [];

  for (const entry of entries) {
    const fullPath = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      files.push(...getMockHtmlFiles(fullPath));
      continue;
    }

    if (entry.name.endsWith(".html")) {
      files.push(fullPath);
    }
  }

  return files.sort();
}

async function collectClientErrors(page: Page): Promise<{
  pageErrors: string[];
  consoleErrors: string[];
}> {
  const pageErrors: string[] = [];
  const consoleErrors: string[] = [];

  // Known acceptable errors when running with file:// protocol
  const ALLOWED_ERROR_PATTERNS = [
    /net::ERR_FAILED/i, // Resource loading fails with file:// protocol (CORS)
    /Failed to load resource/i, // Same for resource loading
    /Not allowed to load local resource/i, // File access restrictions
    /CORS policy/i, // Cross-origin blocked in file:// context
    /Cross origin requests are only supported/i, // Same CORS issue
  ];

  const isAllowedError = (msg: string): boolean => {
    return ALLOWED_ERROR_PATTERNS.some(pattern => pattern.test(msg));
  };

  page.on("pageerror", error => {
    if (!isAllowedError(error.message)) {
      pageErrors.push(error.message);
    }
  });

  page.on("console", (message: ConsoleMessage) => {
    if (message.type() === "error") {
      const text = message.text();
      if (!isAllowedError(text)) {
        consoleErrors.push(text);
      }
    }
  });

  return { pageErrors, consoleErrors };
}

test.describe("Frontend mock hardening", () => {
  test("rbac index only links to pages that exist on disk", async ({
    page,
  }) => {
    const indexFile = path.join(RBAC_ROOT, "index.html");
    await page.goto(fileUrl(indexFile));

    const hrefs = await page
      .locator('a[href$=".html"]')
      .evaluateAll(links =>
        links
          .map(link => link.getAttribute("href") || "")
          .filter(href => href.length > 0),
      );

    const missing = hrefs.filter(href => {
      const resolved = path.resolve(RBAC_ROOT, href);
      return !fs.existsSync(resolved);
    });

    expect(missing).toEqual([]);
  });

  for (const file of getMockHtmlFiles()) {
    const label = path.relative(MOCKS_ROOT, file).split(path.sep).join("/");

    test(`${label} loads without client-side runtime errors`, async ({
      page,
    }) => {
      const errors = await collectClientErrors(page);

      await page.goto(fileUrl(file));
      await page.waitForLoadState("domcontentloaded");

      expect(errors.pageErrors, `${label} page errors`).toEqual([]);
      expect(errors.consoleErrors, `${label} console errors`).toEqual([]);
    });
  }

  for (const file of ROLE_PAGES) {
    // Note: Role pages import ES modules from external files.
    // Using http:// protocol for ES module support via webServer
    test(`${file} inline RBAC self-tests pass`, async ({ page }) => {
      // Use HTTP server for ES module imports; file:// causes CORS issues
      const httpUrl = `http://localhost:3000/mocks/rbac/${path.basename(file)}`;
      await page.goto(httpUrl);
      const results = await page.evaluate(async () => {
        const runner = (
          window as Window & {
            runRbacTests?: () => Promise<{
              passed: number;
              failed: number;
              total: number;
            }>;
          }
        ).runRbacTests;

        if (!runner) {
          // ES modules failed to load
          return { passed: 0, failed: 1, total: 1 };
        }

        return runner();
      });

      expect(results.failed).toBe(0);
      expect(results.total).toBeGreaterThan(0);
    });
  }

  test("api scenario inline self-tests pass", async ({ page }) => {
    await page.goto(fileUrl(path.join(RBAC_ROOT, "api-scenarios.html")));
    const results = await page.evaluate(async () => {
      const runner = (
        window as Window & {
          runApiTests?: () => Promise<{
            passed: number;
            failed: number;
            total: number;
          }>;
        }
      ).runApiTests;

      if (!runner) {
        return { passed: 0, failed: 1, total: 1 };
      }

      return runner();
    });

    expect(results.failed).toBe(0);
    expect(results.total).toBeGreaterThan(0);
  });
});
