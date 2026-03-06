/**
 * @fileoverview TypeScript version of tests/frontend/js/unit/frontend-risk-audit.test.cjs
 * @generated from original JavaScript - manual review recommended
 * @module frontend-risk-audit.test
 */
/* eslint-disable @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-var-requires */

// @ts-check
const {
  collectLineMatches,
  getRouteScriptFiles,
} = require("../helpers/mock-page-audit.cjs");

describe("First-party frontend security risk audit", (): void => {
  const routeScripts = getRouteScriptFiles();

  test("route scripts avoid eval and new Function", (): void => {
    const offenders = collectLineMatches(routeScripts, line => {
      const trimmed = line.trim();
      if (
        trimmed.startsWith("//") ||
        trimmed.startsWith("*") ||
        trimmed.startsWith("/*")
      )
        return false;
      return /\b(?:eval|new Function)\s*\(/.test(line);
    });

    expect(offenders).toEqual([]);
  });

  test("route scripts avoid document.write", (): void => {
    const offenders = collectLineMatches(routeScripts, line =>
      /document\.write\s*\(/.test(line),
    );

    expect(offenders).toEqual([]);
  });

  test("route scripts avoid direct innerHTML-style injection from message or data payloads", (): void => {
    const offenders = collectLineMatches(routeScripts, line => {
      const usesHtmlSink =
        /\.innerHTML\s*=/.test(line) || /insertAdjacentHTML\s*\(/.test(line);
      const usesDynamicPayload =
        /\b(?:msg|message|text|html|data|response|result)\b/.test(line);

      return usesHtmlSink && usesDynamicPayload;
    });

    expect(offenders).toEqual([]);
  });
});
