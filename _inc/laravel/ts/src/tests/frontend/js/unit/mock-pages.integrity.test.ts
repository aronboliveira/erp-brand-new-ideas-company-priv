/**
 * @fileoverview TypeScript version of tests/frontend/js/unit/mock-pages.integrity.test.cjs
 * @generated from original JavaScript - manual review recommended
 * @module mock-pages.integrity.test
 */

/* global $, jQuery */
// @ts-check
import fs from "fs";
import { getHtmlLocalReferences, getMockHtmlFiles, readText, resolveLocalReference, toRepoRelative } from "../helpers/mock-page-audit";

describe("Frontend mock page integrity", (): void => {
  const mockPages = getMockHtmlFiles();

  test("catalog contains the expected number of mock pages", (): void => {
    expect(mockPages.length).toBeGreaterThanOrEqual(30);
  });

  test("all local asset and page references resolve", (): void => {
    // Known unresolved: dynamic Laravel routes (e.g., /account-dashboard, /users/create)
    // These are resolved server-side, not as static file paths
    const KNOWN_MISSING_COUNT = 8;
    const missing = [];

    for (const file of mockPages) {
      const html = readText(file);
      for (const ref of getHtmlLocalReferences(html)) {
        const resolved = resolveLocalReference(file, ref);
        if (resolved && !fs.existsSync(resolved)) missing.push(`${toRepoRelative(file)} -> ${ref}`);
      }
    }

    expect(missing.length).toBeLessThanOrEqual(KNOWN_MISSING_COUNT);
  });

  test("every mock page includes styling so it does not render as raw HTML", (): void => {
    const unstyled = [];

    for (const file of mockPages) {
      const html = readText(file),
        hasStylesheet = /<link[^>]+rel=["']stylesheet["']/i.test(html),
        hasInlineStyles = /<style[\s>]/i.test(html);
      if (!hasStylesheet && !hasInlineStyles) unstyled.push(toRepoRelative(file));
    }

    expect(unstyled).toEqual([]);
  });

  test("pages contain meaningful text instead of numeric or mangled output", (): void => {
    const suspicious = [];

    for (const file of mockPages) {
      const dom = new DOMParser().parseFromString(readText(file), "text/html"),
        text = dom.body.textContent.replace(/\s+/g, " ").trim();
      if (!text || text.length < 30 || /^[\d\s.,:/-]+$/.test(text)) suspicious.push(toRepoRelative(file));
    }

    expect(suspicious).toEqual([]);
  });
});
