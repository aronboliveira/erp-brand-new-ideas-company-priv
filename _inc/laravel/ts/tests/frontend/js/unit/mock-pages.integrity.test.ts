import fs from "fs";
import {
  getHtmlLocalReferences,
  getMockHtmlFiles,
  readText,
  resolveLocalReference,
  toRepoRelative,
} from "../helpers/mock-page-audit";

describe("Frontend mock page integrity", () => {
  const mockPages = getMockHtmlFiles();

  test("catalog contains the expected number of mock pages", () => {
    expect(mockPages.length).toBeGreaterThanOrEqual(30);
  });

  test("all local asset and page references resolve", () => {
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

  test("every mock page includes styling so it does not render as raw HTML", () => {
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

  test("pages contain meaningful text instead of numeric or mangled output", () => {
    const suspicious = [];

    for (const file of mockPages) {
      const dom = new DOMParser().parseFromString(readText(file), "text/html");
      const text = (dom.body?.textContent || "").replace(/\s+/g, " ").trim();

      if (!text || text.length < 30 || /^[\d\s.,:/-]+$/.test(text)) {
        suspicious.push(toRepoRelative(file));
      }
    }

    expect(suspicious).toEqual([]);
  });
});
