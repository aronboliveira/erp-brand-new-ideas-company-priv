/**
 * @file erp-utils.test.cjs
 * @description Unit tests for public/assets/js/core/erp-utils.js
 *
 * ERPUtils is a singleton class that attaches to `window.ERPUtils`.
 * We evaluate the source in jsdom so every `window`, `document`, `navigator`
 * reference resolves as it would in a real browser.
 */

const fs = require("fs");
const path = require("path");

/* ------------------------------------------------------------------ */
/*  Helpers                                                            */
/* ------------------------------------------------------------------ */

const SRC_PATH = path.resolve(
  __dirname,
  "../../../../../public/assets/js/core/erp-utils.js",
);

/**
 * Load erp-utils.js into the current jsdom global scope.
 * Because the file uses private class fields (#), it must be eval'd
 * by the V8 engine directly — vm.runInThisContext handles it.
 */
function loadERPUtils() {
  // Reset singleton between tests
  delete global.ERPUtils;
  delete global.window.ERPUtils;

  const src = fs.readFileSync(SRC_PATH, "utf8");
  (0, eval)(src);
}

/* ------------------------------------------------------------------ */
/*  Setup                                                              */
/* ------------------------------------------------------------------ */

beforeEach(() => {
  // Provide minimal globals ERPUtils might reference
  global.ERPGuard = undefined;
  global.translations = undefined;

  // jsdom provides window, document, navigator, etc.
  // jsdom default location is about:blank — that's fine for most tests

  loadERPUtils();
});

afterEach(() => {
  delete global.ERPUtils;
  delete global.ERPGuard;
  delete global.translations;
});

/* ------------------------------------------------------------------ */
/*  Singleton behaviour                                                */
/* ------------------------------------------------------------------ */

describe("ERPUtils — singleton", () => {
  test("window.ERPUtils is defined after load", () => {
    expect(window.ERPUtils).toBeDefined();
  });

  test("exposes expected public methods", () => {
    const u = window.ERPUtils;
    const methods = [
      "formatNumber",
      "formatCurrency",
      "formatDate",
      "debounce",
      "throttle",
      "deepClone",
      "getQueryParam",
      "setQueryParam",
      "generateId",
      "copyToClipboard",
      "getTranslation",
      "getMsg",
      "isInViewport",
      "scrollToElement",
      "bindClipboardAction",
    ];
    methods.forEach(m => expect(typeof u[m]).toBe("function"));
  });
});

/* ------------------------------------------------------------------ */
/*  formatNumber                                                       */
/* ------------------------------------------------------------------ */

describe("ERPUtils.formatNumber", () => {
  test("formats with default 2 decimal places", () => {
    const result = window.ERPUtils.formatNumber(1234.5);
    // Intl output varies by locale but should contain 1234 and decimals
    expect(result).toMatch(/1[,.]?234/);
  });

  test("respects custom decimal places", () => {
    const result = window.ERPUtils.formatNumber(1234.5678, { decimals: 3 });
    expect(result).toContain("568"); // rounded
  });

  test("respects explicit locale", () => {
    const result = window.ERPUtils.formatNumber(1234.5, { locale: "de-DE" });
    // German uses comma as decimal separator
    expect(result).toContain(",");
  });

  test("handles zero", () => {
    const result = window.ERPUtils.formatNumber(0);
    expect(result).toContain("0");
  });

  test("handles negative numbers", () => {
    const result = window.ERPUtils.formatNumber(-42.1);
    expect(result).toContain("42");
  });
});

/* ------------------------------------------------------------------ */
/*  formatCurrency                                                     */
/* ------------------------------------------------------------------ */

describe("ERPUtils.formatCurrency", () => {
  test("formats USD by default", () => {
    const result = window.ERPUtils.formatCurrency(99.99);
    // Should contain dollar sign or USD indicator
    expect(result).toMatch(/\$|USD/);
  });

  test("formats with explicit currency code", () => {
    const result = window.ERPUtils.formatCurrency(50, { currency: "EUR" });
    expect(result).toMatch(/€|EUR/);
  });

  test("formats BRL with pt-BR locale", () => {
    const result = window.ERPUtils.formatCurrency(100, {
      currency: "BRL",
      locale: "pt-BR",
    });
    expect(result).toMatch(/R\$|BRL/);
  });

  test("handles zero amount", () => {
    const result = window.ERPUtils.formatCurrency(0);
    expect(result).toContain("0");
  });
});

/* ------------------------------------------------------------------ */
/*  formatDate                                                         */
/* ------------------------------------------------------------------ */

describe("ERPUtils.formatDate", () => {
  test("formats a Date object", () => {
    const d = new Date(2024, 0, 15); // Jan 15 2024
    const result = window.ERPUtils.formatDate(d);
    expect(result).toContain("2024");
    expect(result).toMatch(/Jan|1/); // month representation varies
  });

  test("formats an ISO string", () => {
    const result = window.ERPUtils.formatDate("2024-06-01T12:00:00Z");
    expect(result).toContain("2024");
  });

  test("returns empty string for invalid date", () => {
    expect(window.ERPUtils.formatDate("not-a-date")).toBe("");
  });

  test("includes time when option set", () => {
    const result = window.ERPUtils.formatDate(new Date(2024, 5, 1, 14, 30), {
      includeTime: true,
      locale: "en-US",
    });
    // Should contain some time indicator
    expect(result.length).toBeGreaterThan(
      window.ERPUtils.formatDate(new Date(2024, 5, 1), {
        locale: "en-US",
      }).length,
    );
  });
});

/* ------------------------------------------------------------------ */
/*  debounce                                                           */
/* ------------------------------------------------------------------ */

describe("ERPUtils.debounce", () => {
  beforeEach(() => jest.useFakeTimers());
  afterEach(() => jest.useRealTimers());

  test("delays execution", () => {
    const fn = jest.fn();
    const debounced = window.ERPUtils.debounce(fn, 200);

    debounced();
    expect(fn).not.toHaveBeenCalled();

    jest.advanceTimersByTime(200);
    expect(fn).toHaveBeenCalledTimes(1);
  });

  test("resets timer on rapid calls", () => {
    const fn = jest.fn();
    const debounced = window.ERPUtils.debounce(fn, 100);

    debounced();
    jest.advanceTimersByTime(50);
    debounced(); // reset
    jest.advanceTimersByTime(50);
    expect(fn).not.toHaveBeenCalled();

    jest.advanceTimersByTime(50);
    expect(fn).toHaveBeenCalledTimes(1);
  });

  test("passes arguments to the wrapped function", () => {
    const fn = jest.fn();
    const debounced = window.ERPUtils.debounce(fn, 50);

    debounced("a", "b");
    jest.advanceTimersByTime(50);
    expect(fn).toHaveBeenCalledWith("a", "b");
  });
});

/* ------------------------------------------------------------------ */
/*  throttle                                                           */
/* ------------------------------------------------------------------ */

describe("ERPUtils.throttle", () => {
  beforeEach(() => jest.useFakeTimers());
  afterEach(() => jest.useRealTimers());

  test("executes immediately on first call", () => {
    const fn = jest.fn();
    const throttled = window.ERPUtils.throttle(fn, 200);

    throttled();
    expect(fn).toHaveBeenCalledTimes(1);
  });

  test("blocks subsequent calls within the limit", () => {
    const fn = jest.fn();
    const throttled = window.ERPUtils.throttle(fn, 200);

    throttled();
    throttled();
    throttled();
    expect(fn).toHaveBeenCalledTimes(1);
  });

  test("allows call after limit expires", () => {
    const fn = jest.fn();
    const throttled = window.ERPUtils.throttle(fn, 100);

    throttled();
    jest.advanceTimersByTime(100);
    throttled();
    expect(fn).toHaveBeenCalledTimes(2);
  });

  test("passes arguments correctly", () => {
    const fn = jest.fn();
    const throttled = window.ERPUtils.throttle(fn, 100);

    throttled(42, "hello");
    expect(fn).toHaveBeenCalledWith(42, "hello");
  });
});

/* ------------------------------------------------------------------ */
/*  deepClone                                                          */
/* ------------------------------------------------------------------ */

describe("ERPUtils.deepClone", () => {
  test("clones simple objects", () => {
    const obj = { a: 1, b: "two", c: [3] };
    const clone = window.ERPUtils.deepClone(obj);
    expect(clone).toEqual(obj);
    expect(clone).not.toBe(obj);
  });

  test("clone is independent of original (deep)", () => {
    const obj = { nested: { value: "original" } };
    const clone = window.ERPUtils.deepClone(obj);
    clone.nested.value = "modified";
    expect(obj.nested.value).toBe("original");
  });

  test("handles null", () => {
    expect(window.ERPUtils.deepClone(null)).toBeNull();
  });

  test("returns primitives unchanged", () => {
    expect(window.ERPUtils.deepClone(42)).toBe(42);
    expect(window.ERPUtils.deepClone("str")).toBe("str");
    expect(window.ERPUtils.deepClone(true)).toBe(true);
  });

  test("clones arrays", () => {
    const arr = [1, [2, 3], { a: 4 }];
    const clone = window.ERPUtils.deepClone(arr);
    expect(clone).toEqual(arr);
    clone[1].push(99);
    expect(arr[1]).toEqual([2, 3]);
  });
});

/* ------------------------------------------------------------------ */
/*  getQueryParam / setQueryParam                                      */
/* ------------------------------------------------------------------ */

describe("ERPUtils.getQueryParam", () => {
  test("extracts parameter from a URL string", () => {
    const val = window.ERPUtils.getQueryParam(
      "page",
      "http://localhost:8000/items?page=3&sort=asc",
    );
    expect(val).toBe("3");
  });

  test("returns null for missing parameter", () => {
    const val = window.ERPUtils.getQueryParam(
      "missing",
      "http://localhost:8000/items?page=1",
    );
    expect(val).toBeNull();
  });

  test("handles encoded parameter values", () => {
    const val = window.ERPUtils.getQueryParam(
      "q",
      "http://localhost:8000/search?q=hello%20world",
    );
    expect(val).toBe("hello world");
  });
});

describe("ERPUtils.setQueryParam", () => {
  test("sets param and updates history", () => {
    const pushSpy = jest
      .spyOn(window.history, "pushState")
      .mockImplementation(() => {});
    window.ERPUtils.setQueryParam("tab", "billing");
    expect(pushSpy).toHaveBeenCalled();

    const urlArg = String(pushSpy.mock.calls[0][2]);
    expect(urlArg).toContain("tab=billing");
    pushSpy.mockRestore();
  });

  test("uses replaceState when updateHistory=false", () => {
    const replaceSpy = jest
      .spyOn(window.history, "replaceState")
      .mockImplementation(() => {});
    window.ERPUtils.setQueryParam("view", "grid", false);
    expect(replaceSpy).toHaveBeenCalled();

    const urlArg = String(replaceSpy.mock.calls[0][2]);
    expect(urlArg).toContain("view=grid");
    replaceSpy.mockRestore();
  });
});

/* ------------------------------------------------------------------ */
/*  generateId                                                         */
/* ------------------------------------------------------------------ */

describe("ERPUtils.generateId", () => {
  test("returns a non-empty string", () => {
    const id = window.ERPUtils.generateId();
    expect(typeof id).toBe("string");
    expect(id.length).toBeGreaterThan(0);
  });

  test("includes prefix when provided", () => {
    const id = window.ERPUtils.generateId("item");
    expect(id.startsWith("item-")).toBe(true);
  });

  test("generates unique IDs", () => {
    const ids = new Set(
      Array.from({ length: 50 }, () => window.ERPUtils.generateId()),
    );
    expect(ids.size).toBe(50);
  });
});

/* ------------------------------------------------------------------ */
/*  copyToClipboard                                                    */
/* ------------------------------------------------------------------ */

describe("ERPUtils.copyToClipboard", () => {
  test("returns false for empty text", async () => {
    const result = await window.ERPUtils.copyToClipboard("");
    expect(result).toBe(false);
  });

  test("returns false for null text", async () => {
    const result = await window.ERPUtils.copyToClipboard(null);
    expect(result).toBe(false);
  });

  test("copies text using Clipboard API when available", async () => {
    const writeText = jest.fn().mockResolvedValue(undefined);
    Object.defineProperty(navigator, "clipboard", {
      value: { writeText },
      writable: true,
      configurable: true,
    });

    const result = await window.ERPUtils.copyToClipboard("test text", false);
    expect(writeText).toHaveBeenCalledWith("test text");
    expect(result).toBe(true);
  });
});

/* ------------------------------------------------------------------ */
/*  getTranslation / getMsg                                            */
/* ------------------------------------------------------------------ */

describe("ERPUtils.getTranslation", () => {
  test("returns key as fallback when no translations exist", () => {
    // No ERPGuard, no window.translations
    const msg = window.ERPUtils.getTranslation("some_key");
    expect(msg).toBe("some_key");
  });

  test("delegates to ERPGuard.getMsg when available", () => {
    global.ERPGuard = { getMsg: jest.fn().mockReturnValue("translated") };
    // Need to reload to pick up new global
    loadERPUtils();

    const result = window.ERPUtils.getTranslation("test_key");
    expect(result).toBe("translated");
  });

  test("getMsg is an alias for getTranslation", () => {
    const tSpy = jest.spyOn(window.ERPUtils, "getTranslation");
    window.ERPUtils.getMsg("some_key");
    expect(tSpy).toHaveBeenCalledWith("some_key");
    tSpy.mockRestore();
  });
});

/* ------------------------------------------------------------------ */
/*  isInViewport (basic checks)                                        */
/* ------------------------------------------------------------------ */

describe("ERPUtils.isInViewport", () => {
  test("returns false for null element", () => {
    expect(window.ERPUtils.isInViewport(null)).toBe(false);
  });

  test("returns false for undefined element", () => {
    expect(window.ERPUtils.isInViewport(undefined)).toBe(false);
  });
});
