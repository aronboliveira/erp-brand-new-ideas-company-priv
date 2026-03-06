/**
 * @fileoverview TypeScript version of tests/frontend/js/unit/frontend-performance-budgets.test.cjs
 * @generated from original JavaScript - manual review recommended
 * @module frontend-performance-budgets.test
 */
/* eslint-disable @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars, @typescript-eslint/no-var-requires */

/* global $, jQuery */
// @ts-check
const fs = require("fs");
const path = require("path");
const {
  APP_ROOT,
  getMockHtmlFiles,
  getRouteScriptFiles,
  toRepoRelative,
} = require("../helpers/mock-page-audit.cjs");

describe("Frontend performance and stability budgets", (): void => {
  const mockPages = getMockHtmlFiles();
  const routeScripts = getRouteScriptFiles();

  test("mock HTML fixtures stay below 200KB each", (): void => {
    const offenders = [];

    for (const file of mockPages) {
      const sizeKb = Math.round((fs.statSync(file).size / 1024) * 10) / 10;
      if (sizeKb > 200) {
        offenders.push(`${toRepoRelative(file)} (${sizeKb}KB)`);
      }
    }

    expect(offenders).toEqual([]);
  });

  test("first-party route scripts stay below 25KB each", (): void => {
    const offenders = [];

    for (const file of routeScripts) {
      const sizeKb = Math.round((fs.statSync(file).size / 1024) * 10) / 10;
      if (sizeKb > 25) {
        offenders.push(`${toRepoRelative(file)} (${sizeKb}KB)`);
      }
    }

    expect(offenders).toEqual([]);
  });

  test("guest auth form provides password autocomplete hints", (): void => {
    const guestPage = path.join(
      APP_ROOT,
      "tests",
      "frontend",
      "js",
      "pages",
      "mocks",
      "rbac",
      "guest.html",
    );
    const dom = new DOMParser().parseFromString(
      fs.readFileSync(guestPage, "utf8"),
      "text/html",
    );
    const passwordInputs = Array.from(
      dom.querySelectorAll('input[type="password"]'),
    );

    const missingAutocomplete = passwordInputs
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      .filter(input => !input.getAttribute("autocomplete"))
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
      .map(input => input.id || input.name ?? "<anonymous>");

    expect(missingAutocomplete).toEqual([]);
  });
});
