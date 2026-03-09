const fs = require("fs");
const path = require("path");
const {
  APP_ROOT,
  getMockHtmlFiles,
  getRouteScriptFiles,
  toRepoRelative,
} = require("../helpers/mock-page-audit.cjs");

describe("Frontend performance and stability budgets", () => {
  const mockPages = getMockHtmlFiles();
  const routeScripts = getRouteScriptFiles();

  test("mock HTML fixtures stay below 200KB each", () => {
    const offenders = [];

    for (const file of mockPages) {
      const sizeKb = Math.round((fs.statSync(file).size / 1024) * 10) / 10;
      if (sizeKb > 200) {
        offenders.push(`${toRepoRelative(file)} (${sizeKb}KB)`);
      }
    }

    expect(offenders).toEqual([]);
  });

  test("first-party route scripts stay below 25KB each", () => {
    const offenders = [];

    for (const file of routeScripts) {
      const sizeKb = Math.round((fs.statSync(file).size / 1024) * 10) / 10;
      if (sizeKb > 25) {
        offenders.push(`${toRepoRelative(file)} (${sizeKb}KB)`);
      }
    }

    expect(offenders).toEqual([]);
  });

  test("guest auth form provides password autocomplete hints", () => {
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
      .filter(input => !input.getAttribute("autocomplete"))
      .map(input => input.id || input.name || "<anonymous>");

    expect(missingAutocomplete).toEqual([]);
  });
});
