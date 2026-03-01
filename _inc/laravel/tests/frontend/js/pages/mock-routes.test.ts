/**
 * @file mock-routes.test.ts
 * @description Jest tests for the generated mock route HTML pages.
 * Validates that every category page has the correct structure:
 *  - Timestamp heading
 *  - Route table with correct columns
 *  - JS fetch wiring for each route
 *  - Bulk controls
 *  - Filter input
 *
 * Generated: 2025-02-28
 */
import { describe, it, expect, beforeEach } from "@jest/globals";
import fs from "fs";
import path from "path";
import { resetDOM } from "../setup";

const MOCKS_DIR = path.join(__dirname, "mocks");

/** Get all category HTML files (exclude index.html) */
function getMockFiles(): string[] {
  if (!fs.existsSync(MOCKS_DIR)) return [];
  return fs
    .readdirSync(MOCKS_DIR)
    .filter(f => f.endsWith(".html") && f !== "index.html")
    .sort();
}

/** Load an HTML file into jsdom */
function loadMockPage(filename: string): void {
  const html = fs.readFileSync(path.join(MOCKS_DIR, filename), "utf-8");
  const bodyMatch = html.match(/<body[^>]*>([\s\S]*)<\/body>/i);
  if (bodyMatch) {
    document.body.innerHTML = bodyMatch[1];
  } else {
    document.body.innerHTML = html;
  }
  // Extract and set head elements
  const headMatch = html.match(/<head[^>]*>([\s\S]*)<\/head>/i);
  if (headMatch) {
    const titleMatch = headMatch[1].match(/<title[^>]*>([\s\S]*?)<\/title>/i);
    if (titleMatch) {
      document.title = titleMatch[1];
    }
  }
}

const mockFiles = getMockFiles();

describe("Mock Route Pages — Index", () => {
  it("should have an index.html", () => {
    const indexPath = path.join(MOCKS_DIR, "index.html");
    expect(fs.existsSync(indexPath)).toBe(true);
  });

  it("index should list all category pages", () => {
    const indexHtml = fs.readFileSync(
      path.join(MOCKS_DIR, "index.html"),
      "utf-8",
    );
    for (const f of mockFiles) {
      expect(indexHtml).toContain(f);
    }
  });

  it("index should have a timestamp", () => {
    const indexHtml = fs.readFileSync(
      path.join(MOCKS_DIR, "index.html"),
      "utf-8",
    );
    expect(indexHtml).toMatch(/Generated:\s*\d{4}-\d{2}-\d{2}/);
  });
});

describe("Mock Route Pages — Structure", () => {
  if (mockFiles.length === 0) {
    it.skip("no mock files found", () => {});
    return;
  }

  describe.each(mockFiles)("%s", filename => {
    beforeEach(() => {
      resetDOM();
      loadMockPage(filename);
    });

    it("should have a title", () => {
      expect(document.title).toMatch(/Mock Test Page/);
    });

    it("should have a timestamp paragraph", () => {
      const ts = document.querySelector(".timestamp");
      expect(ts).toBeTruthy();
      expect(ts?.textContent).toMatch(/Generated:\s*\d{4}-\d{2}-\d{2}/);
    });

    it("should have a route summary", () => {
      const summary = document.querySelector(".summary");
      expect(summary).toBeTruthy();
      expect(summary?.textContent).toMatch(/\d+\s+route/);
    });

    it("should have a route table with headers", () => {
      const table = document.querySelector("#route-table");
      expect(table).toBeTruthy();
      const headers = table!.querySelectorAll("thead th");
      const headerTexts = Array.from(headers).map(h =>
        h.textContent?.trim().toLowerCase(),
      );
      expect(headerTexts).toContain("method");
      expect(headerTexts).toContain("uri");
      expect(headerTexts).toContain("name");
      expect(headerTexts).toContain("auth");
      expect(headerTexts).toContain("test");
    });

    it("should have at least one route row", () => {
      const rows = document.querySelectorAll("#route-table tbody tr");
      expect(rows.length).toBeGreaterThan(0);
    });

    it("each route row should have a data-uri attribute", () => {
      const rows = document.querySelectorAll("#route-table tbody tr");
      rows.forEach(row => {
        expect(row.getAttribute("data-uri")).toBeTruthy();
        expect(row.getAttribute("data-uri")!.startsWith("/")).toBe(true);
      });
    });

    it("each route row should have a fetch button", () => {
      const buttons = document.querySelectorAll(".btn-fetch");
      const rows = document.querySelectorAll("#route-table tbody tr");
      expect(buttons.length).toBe(rows.length);
    });

    it("should have bulk controls", () => {
      const fetchAll = document.querySelector("#btn-fetch-all");
      const clear = document.querySelector("#btn-clear");
      expect(fetchAll).toBeTruthy();
      expect(clear).toBeTruthy();
    });

    it("should have a filter input", () => {
      const filter = document.querySelector(
        "#filter",
      ) as HTMLInputElement | null;
      expect(filter).toBeTruthy();
      expect(filter?.type).toBe("text");
    });

    it("should have a results container", () => {
      const results = document.querySelector("#results");
      expect(results).toBeTruthy();
    });

    it("each route should have auth badge (Auth or Public)", () => {
      const badges = document.querySelectorAll(".badge");
      expect(badges.length).toBeGreaterThan(0);
      badges.forEach(badge => {
        const text = badge.textContent?.trim();
        expect(["Auth", "Public"]).toContain(text);
      });
    });

    it("route methods should be valid HTTP methods", () => {
      const methodCells = document.querySelectorAll(
        "#route-table tbody tr td:first-child code",
      );
      const validMethods = [
        "GET",
        "POST",
        "PUT",
        "PATCH",
        "DELETE",
        "OPTIONS",
        "ANY",
      ];
      methodCells.forEach(cell => {
        const methods = cell.textContent!.split(",").map(m => m.trim());
        methods.forEach(m => {
          expect(validMethods).toContain(m);
        });
      });
    });

    it("should have inline script with getRouteInfo function", () => {
      const html = fs.readFileSync(path.join(MOCKS_DIR, filename), "utf-8");
      expect(html).toContain("function getRouteInfo(idx)");
      expect(html).toContain("async function fetchRoute(idx)");
    });
  });
});

describe("Mock Route Pages — Completeness", () => {
  const expectedCategories = [
    "auth",
    "crm",
    "dashboard",
    "finance",
    "hrm",
    "projects",
    "pos",
    "settings",
    "users",
    "recruitment",
    "landingpage",
    "products",
    "proposals",
    "contracts",
    "purchases",
    "meetings",
    "chat",
    "reports",
    "forms",
    "support",
    "plans",
  ];

  it.each(expectedCategories)("should have a mock page for %s", category => {
    const filename = `${category}.html`;
    expect(mockFiles).toContain(filename);
  });

  it("should cover at least 1400 routes total", () => {
    let totalRows = 0;
    for (const f of mockFiles) {
      const html = fs.readFileSync(path.join(MOCKS_DIR, f), "utf-8");
      const matches = html.match(/data-route-idx="/g);
      totalRows += matches ? matches.length : 0;
    }
    expect(totalRows).toBeGreaterThanOrEqual(1400);
  });
});
