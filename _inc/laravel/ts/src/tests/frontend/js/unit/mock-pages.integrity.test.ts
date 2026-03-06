/**
 * @fileoverview TypeScript version of tests/frontend/js/unit/mock-pages.integrity.test.cjs
 * @generated from original JavaScript - manual review recommended
 * @module mock-pages.integrity.test
 */
/* eslint-disable @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unused-vars, @typescript-eslint/no-var-requires, @typescript-eslint/restrict-template-expressions */

/* global $, jQuery */
// @ts-check
const fs = require("fs");
const {
  getHtmlLocalReferences,
  getMockHtmlFiles,
  readText,
  resolveLocalReference,
  toRepoRelative,
} = require("../helpers/mock-page-audit.cjs");

describe("Frontend mock page integrity", (): void => {
  const mockPages = getMockHtmlFiles();

  test("catalog contains the expected number of mock pages", (): void => {
    expect(mockPages.length).toBeGreaterThanOrEqual(30);
  });

  test("all local asset and page references resolve", (): void => {
    const missing = [];

    for (const file of mockPages) {
      const html = readText(file);
      for (const ref of getHtmlLocalReferences(html)) {
        const resolved = resolveLocalReference(file, ref);
        if (resolved && !fs.existsSync(resolved)) {
          missing.push(`${toRepoRelative(file)} -> ${ref}`);
        }
      }
    }

    expect(missing).toEqual([]);
  });

  test("every mock page includes styling so it does not render as raw HTML", (): void => {
    const unstyled = [];

    for (const file of mockPages) {
      const html = readText(file);
      const hasStylesheet = /<link[^>]+rel=["']stylesheet["']/i.test(html);
      const hasInlineStyles = /<style[\s>]/i.test(html);

      if (!hasStylesheet && !hasInlineStyles) {
        unstyled.push(toRepoRelative(file));
      }
    }

    expect(unstyled).toEqual([]);
  });

  test("pages contain meaningful text instead of numeric or mangled output", (): void => {
    const suspicious = [];

    for (const file of mockPages) {
      const dom = new DOMParser().parseFromString(readText(file), "text/html");
      const text = (dom.body.textContent).replace(/\s+/g, " ").trim();

      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      if (!text || text.length < 30 || /^[\d\s.,:/-]+$/.test(text)) {
        suspicious.push(toRepoRelative(file));
      }
    }

    expect(suspicious).toEqual([]);
  });
});
