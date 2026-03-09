/**
 * @file erp-guard.test.cjs
 * @description Unit tests for public/assets/js/core/erp-guard.js
 *
 * ERPGuard is an IIFE-wrapped singleton that attaches to `window.ERPGuard`.
 * The file uses private class fields (#) so we eval it in jsdom via
 * vm.runInThisContext to keep V8 happy.
 */

const fs = require("fs");
const path = require("path");

/* ------------------------------------------------------------------ */
/*  Helpers                                                            */
/* ------------------------------------------------------------------ */

const SRC_PATH = path.resolve(
  __dirname,
  "../../../../../public/assets/js/core/erp-guard.js",
);

/**
 * Provide minimal bootstrap stubs so ERPGuard initialises without errors.
 */
function stubBootstrap() {
  global.bootstrap = {
    Toast: class Toast {
      constructor() {}
      show() {}
      hide() {}
    },
    Modal: class Modal {
      constructor() {}
      show() {}
      hide() {}
    },
  };
}

/**
 * Load erp-guard.js into the current jsdom global scope.
 */
function loadERPGuard() {
  // Reset any prior instance — the singleton stores itself on a static #instance
  delete global.ERPGuard;
  delete global.window.ERPGuard;

  const src = fs.readFileSync(SRC_PATH, "utf8");
  (0, eval)(src);
}

/* ------------------------------------------------------------------ */
/*  Setup                                                              */
/* ------------------------------------------------------------------ */

beforeEach(() => {
  // Provide a body for DOM operations
  document.body.innerHTML = "";
  document.documentElement.lang = "en";

  // Storage mocks (jsdom provides them but let's be explicit)
  const store = {};
  jest
    .spyOn(Storage.prototype, "getItem")
    .mockImplementation(k => store[k] ?? null);
  jest.spyOn(Storage.prototype, "setItem").mockImplementation((k, v) => {
    store[k] = String(v);
  });

  // Console stubs to keep output clean
  jest.spyOn(console, "info").mockImplementation(() => {});
  jest.spyOn(console, "warn").mockImplementation(() => {});

  stubBootstrap();
  loadERPGuard();
});

afterEach(() => {
  delete global.ERPGuard;
  delete global.bootstrap;
  jest.restoreAllMocks();
});

/* ------------------------------------------------------------------ */
/*  Singleton                                                          */
/* ------------------------------------------------------------------ */

describe("ERPGuard — singleton", () => {
  test("window.ERPGuard is defined after load", () => {
    expect(window.ERPGuard).toBeDefined();
  });

  test("ERPGuard exposes core public methods", () => {
    const g = window.ERPGuard;
    expect(typeof g.getLocale).toBe("function");
    expect(typeof g.setLocale).toBe("function");
    expect(typeof g.getMsg).toBe("function");
    expect(typeof g.showToast).toBe("function");
    expect(typeof g.isInvalidUrl).toBe("function");
    expect(typeof g.hasBootstrap).toBe("function");
    expect(typeof g.hasToast).toBe("function");
    expect(typeof g.hasModal).toBe("function");
  });
});

/* ------------------------------------------------------------------ */
/*  Locale detection & management                                      */
/* ------------------------------------------------------------------ */

describe("ERPGuard — locale", () => {
  test("getLocale returns a string", () => {
    expect(typeof window.ERPGuard.getLocale()).toBe("string");
  });

  test("default locale is 'en' when no other source is available", () => {
    // We set document.documentElement.lang = "en" in beforeEach
    expect(window.ERPGuard.getLocale()).toBe("en");
  });

  test("setLocale changes the locale", () => {
    window.ERPGuard.setLocale("pt");
    expect(window.ERPGuard.getLocale()).toBe("pt");
  });

  test("setLocale ignores unsupported locales", () => {
    window.ERPGuard.setLocale("xx");
    expect(window.ERPGuard.getLocale()).not.toBe("xx");
  });

  test("setLocale persists to localStorage", () => {
    window.ERPGuard.setLocale("fr");
    expect(localStorage.setItem).toHaveBeenCalledWith("locale", "fr");
  });

  test("setLocale returns instance for chaining", () => {
    const result = window.ERPGuard.setLocale("es");
    expect(result).toBe(window.ERPGuard);
  });
});

/* ------------------------------------------------------------------ */
/*  getMsg (localized messages)                                        */
/* ------------------------------------------------------------------ */

describe("ERPGuard — getMsg", () => {
  test("returns English message for known key", () => {
    const msg = window.ERPGuard.getMsg("error");
    expect(msg).toBe("An error occurred");
  });

  test("returns Portuguese message after setLocale(pt)", () => {
    window.ERPGuard.setLocale("pt");
    expect(window.ERPGuard.getMsg("error")).toBe("Ocorreu um erro");
  });

  test("returns Spanish message after setLocale(es)", () => {
    window.ERPGuard.setLocale("es");
    expect(window.ERPGuard.getMsg("success")).toBe(
      "Operación completada con éxito",
    );
  });

  test("returns key as fallback for unknown key", () => {
    expect(window.ERPGuard.getMsg("nonexistent_key")).toBe("nonexistent_key");
  });

  test("returns custom fallback when provided", () => {
    expect(window.ERPGuard.getMsg("nope", "custom fallback")).toBe(
      "custom fallback",
    );
  });
});

/* ------------------------------------------------------------------ */
/*  Bootstrap detection                                                */
/* ------------------------------------------------------------------ */

describe("ERPGuard — Bootstrap detection", () => {
  test("hasBootstrap returns true when bootstrap global has Toast+Modal", () => {
    expect(window.ERPGuard.hasBootstrap()).toBe(true);
  });

  test("hasToast returns true with bootstrap.Toast", () => {
    expect(window.ERPGuard.hasToast()).toBe(true);
  });

  test("hasModal returns true with bootstrap.Modal", () => {
    expect(window.ERPGuard.hasModal()).toBe(true);
  });

  test("hasBootstrap returns false when bootstrap is undefined", () => {
    delete global.bootstrap;
    expect(window.ERPGuard.hasBootstrap()).toBe(false);
  });

  test("hasToast returns false when bootstrap is undefined", () => {
    delete global.bootstrap;
    expect(window.ERPGuard.hasToast()).toBe(false);
  });

  test("hasModal returns false when bootstrap is undefined", () => {
    delete global.bootstrap;
    expect(window.ERPGuard.hasModal()).toBe(false);
  });
});

/* ------------------------------------------------------------------ */
/*  isInvalidUrl                                                       */
/* ------------------------------------------------------------------ */

describe("ERPGuard — isInvalidUrl", () => {
  test("null / undefined / empty → invalid", () => {
    expect(window.ERPGuard.isInvalidUrl(null)).toBe(true);
    expect(window.ERPGuard.isInvalidUrl(undefined)).toBe(true);
    expect(window.ERPGuard.isInvalidUrl("")).toBe(true);
  });

  test("javascript: scheme → invalid", () => {
    expect(window.ERPGuard.isInvalidUrl("javascript:alert(1)")).toBe(true);
  });

  test("data: scheme → invalid", () => {
    expect(window.ERPGuard.isInvalidUrl("data:text/html,<h1>hi</h1>")).toBe(
      true,
    );
  });

  test("vbscript: scheme → invalid", () => {
    expect(window.ERPGuard.isInvalidUrl("vbscript:msgbox")).toBe(true);
  });

  test("valid HTTPS URL → valid", () => {
    expect(window.ERPGuard.isInvalidUrl("https://example.com")).toBe(false);
  });

  test("valid HTTP URL → valid (default)", () => {
    expect(window.ERPGuard.isInvalidUrl("http://example.com")).toBe(false);
  });

  test("relative URL → valid by default", () => {
    expect(window.ERPGuard.isInvalidUrl("/dashboard")).toBe(false);
    expect(window.ERPGuard.isInvalidUrl("./page")).toBe(false);
  });

  test("requireHttps rejects HTTP", () => {
    expect(
      window.ERPGuard.isInvalidUrl("http://example.com", {
        requireHttps: true,
      }),
    ).toBe(true);
  });

  test("requireHttps allows HTTPS", () => {
    expect(
      window.ERPGuard.isInvalidUrl("https://example.com", {
        requireHttps: true,
      }),
    ).toBe(false);
  });

  test("allowedHosts blocks non-listed hosts", () => {
    expect(
      window.ERPGuard.isInvalidUrl("https://evil.com", {
        allowedHosts: ["example.com"],
      }),
    ).toBe(true);
  });

  test("allowedHosts allows listed hosts", () => {
    expect(
      window.ERPGuard.isInvalidUrl("https://example.com/path", {
        allowedHosts: ["example.com"],
      }),
    ).toBe(false);
  });
});

/* ------------------------------------------------------------------ */
/*  showToast                                                          */
/* ------------------------------------------------------------------ */

describe("ERPGuard — showToast", () => {
  test("returns instance for chaining", () => {
    const result = window.ERPGuard.showToast("test", "info");
    expect(result).toBe(window.ERPGuard);
  });

  test("does nothing when message is empty", () => {
    const result = window.ERPGuard.showToast("");
    expect(result).toBe(window.ERPGuard);
  });

  test("creates a toast element in the DOM", () => {
    window.ERPGuard.showToast("Hello!", "success");
    const toastEls = document.querySelectorAll(".toast");
    expect(toastEls.length).toBeGreaterThanOrEqual(1);
  });

  test("falls back to alert when bootstrap unavailable", () => {
    delete global.bootstrap;
    const alertSpy = jest.spyOn(global, "alert").mockImplementation(() => {});
    window.ERPGuard.showToast("fallback msg", "error");
    expect(alertSpy).toHaveBeenCalledWith(
      expect.stringContaining("fallback msg"),
    );
    alertSpy.mockRestore();
  });
});

/* ------------------------------------------------------------------ */
/*  Convenience toast methods                                          */
/* ------------------------------------------------------------------ */

describe("ERPGuard — convenience toast methods", () => {
  test("success() calls showToast with 'success' type", () => {
    const spy = jest.spyOn(window.ERPGuard, "showToast");
    window.ERPGuard.success("ok");
    expect(spy).toHaveBeenCalledWith("ok", "success", {});
    spy.mockRestore();
  });

  test("error() calls showToast with 'error' type", () => {
    const spy = jest.spyOn(window.ERPGuard, "showToast");
    window.ERPGuard.error("fail");
    expect(spy).toHaveBeenCalledWith("fail", "error", {});
    spy.mockRestore();
  });

  test("warning() calls showToast with 'warning' type", () => {
    const spy = jest.spyOn(window.ERPGuard, "showToast");
    window.ERPGuard.warning("warn");
    expect(spy).toHaveBeenCalledWith("warn", "warning", {});
    spy.mockRestore();
  });

  test("info() calls showToast with 'info' type", () => {
    const spy = jest.spyOn(window.ERPGuard, "showToast");
    window.ERPGuard.info("note");
    expect(spy).toHaveBeenCalledWith("note", "info", {});
    spy.mockRestore();
  });

  test("error() with null message uses default error message", () => {
    const spy = jest.spyOn(window.ERPGuard, "showToast");
    window.ERPGuard.error(null);
    expect(spy).toHaveBeenCalledWith("An error occurred", "error", {});
    spy.mockRestore();
  });
});

/* ------------------------------------------------------------------ */
/*  DOM containers                                                     */
/* ------------------------------------------------------------------ */

describe("ERPGuard — DOM container creation", () => {
  test("creates toast container in body", () => {
    // showToast triggers #ensureContainers
    window.ERPGuard.showToast("trigger", "info");
    const container = document.getElementById("erp-toast-container");
    expect(container).not.toBeNull();
    expect(container.getAttribute("aria-live")).toBe("polite");
  });

  test("creates modal container in body", () => {
    window.ERPGuard.showToast("trigger", "info");
    const container = document.getElementById("erp-modal-container");
    expect(container).not.toBeNull();
  });
});
