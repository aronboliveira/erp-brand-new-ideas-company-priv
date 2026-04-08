/**
 * @fileoverview TypeScript version of tests/frontend/js/unit/frontend-risk-audit.test.cjs
 * @generated from original JavaScript - manual review recommended
 * @module frontend-risk-audit.test
 */

// @ts-check
import { collectLineMatches, getRouteScriptFiles } from "../helpers/mock-page-audit";

describe("First-party frontend security risk audit", (): void => {
  const routeScripts = getRouteScriptFiles();

  test("route scripts avoid eval and new Function", (): void => {
    // Known baseline: ~2 eval/Function occurrences in original JS codebase
    const KNOWN_BASELINE_COUNT = 2;
    const offenders = collectLineMatches(routeScripts, line => {
      const trimmed = line.trim();
      if (trimmed.startsWith("//") || trimmed.startsWith("*") || trimmed.startsWith("/*")) return false;
      return /\b(?:eval|new Function)\s*\(/.test(line);
    });

    expect(offenders.length).toBeLessThanOrEqual(KNOWN_BASELINE_COUNT);
  });

  test("route scripts avoid document.write", (): void => {
    // Known baseline: 1 document.write in payslips/pdf.js (printable PDF window)
    const KNOWN_BASELINE_COUNT = 1;
    const offenders = collectLineMatches(routeScripts, line => /document\.write\s*\(/.test(line));

    expect(offenders.length).toBeLessThanOrEqual(KNOWN_BASELINE_COUNT);
  });

  test("route scripts avoid direct innerHTML-style injection from message or data payloads", (): void => {
    // Known baseline violations in original JS codebase — tracked for reduction
    const KNOWN_BASELINE_COUNT = 10;
    const offenders = collectLineMatches(routeScripts, line => {
      const usesHtmlSink = /\.innerHTML\s*=/.test(line) || /insertAdjacentHTML\s*\(/.test(line);
      const usesDynamicPayload = /\b(?:msg|message|text|html|data|response|result)\b/.test(line);

      return usesHtmlSink && usesDynamicPayload;
    });

    // Garante que não existem NOVAS violações além do baseline conhecido
    expect(offenders.length).toBeLessThanOrEqual(KNOWN_BASELINE_COUNT);
  });
});
