/**
 * @file route-guard.test.cjs
 * @description Unit tests for public/assets/js/core/route-guard.js
 *
 * route-guard.js is a deprecated backward-compatibility shim.
 * When ERPGuard exists → it creates a thin RouteGuard proxy.
 * When ERPGuard is absent → it provides a standalone implementation.
 *
 * We test both code paths.
 */

const fs = require("fs");
const path = require("path");

/* ------------------------------------------------------------------ */
/*  Helpers                                                            */
/* ------------------------------------------------------------------ */

<<<<<<< HEAD
const GUARD_SRC_PATH = path.resolve(__dirname, "../../../../../public/assets/js/core/erp-guard.js");

const ROUTE_SRC_PATH = path.resolve(__dirname, "../../../../../public/assets/js/core/route-guard.js");
=======
const GUARD_SRC_PATH = path.resolve(
  __dirname,
  "../../../../../public/assets/js/core/erp-guard.js",
);

const ROUTE_SRC_PATH = path.resolve(
  __dirname,
  "../../../../../public/assets/js/core/route-guard.js",
);
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)

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

function loadScript(filePath) {
  const src = fs.readFileSync(filePath, "utf8");
  (0, eval)(src);
}

/* ================================================================== */
/*  PATH A — ERPGuard exists (compatibility proxy)                     */
/* ================================================================== */

describe("route-guard.js — proxy mode (ERPGuard loaded first)", () => {
  beforeEach(() => {
    document.body.innerHTML = "";
    document.documentElement.lang = "en";

    const store = {};
<<<<<<< HEAD
    jest.spyOn(Storage.prototype, "getItem").mockImplementation(k => store[k] ?? null);
=======
    jest
      .spyOn(Storage.prototype, "getItem")
      .mockImplementation(k => store[k] ?? null);
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
    jest.spyOn(Storage.prototype, "setItem").mockImplementation((k, v) => {
      store[k] = String(v);
    });
    jest.spyOn(console, "info").mockImplementation(() => {});
    jest.spyOn(console, "warn").mockImplementation(() => {});

    stubBootstrap();

    // Load ERPGuard first, then route-guard
    delete global.ERPGuard;
    delete global.window.ERPGuard;
    delete global.RouteGuard;
    delete global.window.RouteGuard;

    loadScript(GUARD_SRC_PATH);
    loadScript(ROUTE_SRC_PATH);
  });

  afterEach(() => {
    delete global.ERPGuard;
    delete global.RouteGuard;
    delete global.bootstrap;
    jest.restoreAllMocks();
  });

  test("window.RouteGuard is defined", () => {
    expect(window.RouteGuard).toBeDefined();
  });

  test("RouteGuard.showToast delegates to ERPGuard.showToast", () => {
    const spy = jest.spyOn(window.ERPGuard, "showToast");
    window.RouteGuard.showToast("hello", "success");
    expect(spy).toHaveBeenCalledWith("hello", "success");
    spy.mockRestore();
  });

  test("RouteGuard.isInvalidUrl delegates to ERPGuard.isInvalidUrl", () => {
    expect(window.RouteGuard.isInvalidUrl("javascript:void(0)")).toBe(true);
    expect(window.RouteGuard.isInvalidUrl("https://example.com")).toBe(false);
  });

  test("RouteGuard.getMsg delegates to ERPGuard.getMsg", () => {
    const result = window.RouteGuard.getMsg(null, "error");
    // ERPGuard.getMsg(key, fallback) — first arg is el in proxy mapping
    expect(typeof result).toBe("string");
  });

  test("RouteGuard.init is a no-op function", () => {
    expect(() => window.RouteGuard.init()).not.toThrow();
  });

<<<<<<< HEAD
  test("RouteGuard.animations are no-op functions (return Promise for null el)", async () => {
    // Animations return Promise.resolve() when called without valid element
    await expect(window.RouteGuard.animations.fadeIn()).resolves.toBeUndefined();
    await expect(window.RouteGuard.animations.fadeOut()).resolves.toBeUndefined();
    await expect(window.RouteGuard.animations.slideDown()).resolves.toBeUndefined();
    await expect(window.RouteGuard.animations.slideUp()).resolves.toBeUndefined();
    expect(() => window.RouteGuard.animations.addAnimation()).not.toThrow();
=======
  test("RouteGuard.animations are no-op functions", () => {
    expect(window.RouteGuard.animations.fadeIn()).toBeUndefined();
    expect(window.RouteGuard.animations.fadeOut()).toBeUndefined();
    expect(window.RouteGuard.animations.slideDown()).toBeUndefined();
    expect(window.RouteGuard.animations.slideUp()).toBeUndefined();
    expect(window.RouteGuard.animations.addAnimation()).toBeUndefined();
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
  });

  test("RouteGuard exposes expected constants", () => {
    expect(window.RouteGuard.TOAST_CONTAINER_ID).toBe("erp-toast-container");
    expect(window.RouteGuard.DATA_GUARD_MSG).toBe("data-guard-msg");
    expect(window.RouteGuard.DATA_SV_LOCALIZED).toBe("data-sv-localized");
    expect(window.RouteGuard.DATA_LISTENER_ACTIVE).toBe("data-listener-active");
    expect(window.RouteGuard.DATA_FAILED_ROUTE).toBe("data-failed-route");
  });
});

/* ================================================================== */
/*  PATH B — ERPGuard absent (standalone implementation)               */
/* ================================================================== */

describe("route-guard.js — standalone mode (no ERPGuard)", () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <meta name="csrf-token" content="test-token-456">
    `;
    document.documentElement.lang = "en";

    const store = {};
<<<<<<< HEAD
    jest.spyOn(Storage.prototype, "getItem").mockImplementation(k => store[k] ?? null);
=======
    jest
      .spyOn(Storage.prototype, "getItem")
      .mockImplementation(k => store[k] ?? null);
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
    jest.spyOn(Storage.prototype, "setItem").mockImplementation((k, v) => {
      store[k] = String(v);
    });
    jest.spyOn(console, "info").mockImplementation(() => {});
    jest.spyOn(console, "warn").mockImplementation(() => {});
    jest.spyOn(console, "error").mockImplementation(() => {});

    stubBootstrap();

    // Ensure NO ERPGuard before loading route-guard
    delete global.ERPGuard;
    delete global.window.ERPGuard;
    delete global.RouteGuard;
    delete global.window.RouteGuard;

    loadScript(ROUTE_SRC_PATH);
  });

  afterEach(() => {
    delete global.ERPGuard;
    delete global.RouteGuard;
    delete global.bootstrap;
    jest.restoreAllMocks();
  });

  test("window.RouteGuard is defined", () => {
    expect(window.RouteGuard).toBeDefined();
  });

  /* ---- isInvalidUrl (standalone) ---- */

  test("isInvalidUrl: empty / '#' → true", () => {
    expect(window.RouteGuard.isInvalidUrl("")).toBe(true);
    expect(window.RouteGuard.isInvalidUrl("#")).toBe(true);
    expect(window.RouteGuard.isInvalidUrl(null)).toBe(true);
    expect(window.RouteGuard.isInvalidUrl(undefined)).toBe(true);
  });

  test("isInvalidUrl: valid URL → false", () => {
    expect(window.RouteGuard.isInvalidUrl("https://example.com")).toBe(false);
    expect(window.RouteGuard.isInvalidUrl("/dashboard")).toBe(false);
  });

  /* ---- showToast (standalone) ---- */

  test("showToast creates a toast element when bootstrap available", () => {
    // Standalone hasBootstrap() checks for a bootstrap CSS link tag
<<<<<<< HEAD
    document.head.innerHTML = '<link rel="stylesheet" href="/css/bootstrap.min.css">';
=======
    document.head.innerHTML =
      '<link rel="stylesheet" href="/css/bootstrap.min.css">';
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
    window.RouteGuard.showToast("Alert!", "error");
    const container = document.getElementById("np-toast-container");
    expect(container).not.toBeNull();
    const toasts = container.querySelectorAll(".toast");
    expect(toasts.length).toBeGreaterThanOrEqual(1);
  });

  test("showToast falls back to alert when no bootstrap", () => {
    delete global.bootstrap;
    const alertSpy = jest.spyOn(global, "alert").mockImplementation(() => {});
    window.RouteGuard.showToast("fallback");
    expect(alertSpy).toHaveBeenCalledWith("fallback");
    alertSpy.mockRestore();
  });

  /* ---- csrfToken ---- */

  test("csrfToken reads from meta tag", () => {
    expect(window.RouteGuard.csrfToken()).toBe("test-token-456");
  });

  /* ---- safeFetch ---- */

  test("safeFetch rejects invalid URLs", async () => {
    const result = await window.RouteGuard.safeFetch("");
    expect(result.ok).toBe(false);
    expect(result.error).toBe("Invalid URL");
  });

  test("safeFetch rejects '#' URLs", async () => {
    const result = await window.RouteGuard.safeFetch("#");
    expect(result.ok).toBe(false);
  });

  /* ---- guardById ---- */

  test("guardById attaches listener to element", () => {
<<<<<<< HEAD
    document.body.insertAdjacentHTML("beforeend", '<a id="test-link" href="#">Link</a>');
=======
    document.body.insertAdjacentHTML(
      "beforeend",
      '<a id="test-link" href="#">Link</a>',
    );
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
    window.RouteGuard.guardById("test-link");
    const el = document.getElementById("test-link");
    expect(el.getAttribute("data-listener-active")).toBe("true");
  });

  test("guardById is a no-op for missing element", () => {
    expect(() => window.RouteGuard.guardById("nonexistent")).not.toThrow();
  });

  /* ---- guardMultiple ---- */

  test("guardMultiple guards multiple elements", () => {
<<<<<<< HEAD
    document.body.insertAdjacentHTML("beforeend", '<a id="lnk1" href="#">L1</a><a id="lnk2" href="#">L2</a>');
    window.RouteGuard.guardMultiple("lnk1", "lnk2");
    expect(document.getElementById("lnk1").getAttribute("data-listener-active")).toBe("true");
    expect(document.getElementById("lnk2").getAttribute("data-listener-active")).toBe("true");
=======
    document.body.insertAdjacentHTML(
      "beforeend",
      '<a id="lnk1" href="#">L1</a><a id="lnk2" href="#">L2</a>',
    );
    window.RouteGuard.guardMultiple("lnk1", "lnk2");
    expect(
      document.getElementById("lnk1").getAttribute("data-listener-active"),
    ).toBe("true");
    expect(
      document.getElementById("lnk2").getAttribute("data-listener-active"),
    ).toBe("true");
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
  });

  /* ---- getMsg standalone ---- */

  test("getMsg returns fallback for unlocalized element", () => {
    const el = document.createElement("div");
    const msg = window.RouteGuard.getMsg(el, "route_unavailable");
    // Without window.translations, falls back to "# ERROR"
    expect(typeof msg).toBe("string");
  });

  test("getMsg reads data-guard-msg from localized element", () => {
    const el = document.createElement("div");
    el.setAttribute("data-sv-localized", "true");
    el.setAttribute("data-guard-msg", "Custom error");
    expect(window.RouteGuard.getMsg(el, "anything")).toBe("Custom error");
  });

  /* ---- logError ---- */

  test("logError logs to console.error", () => {
    window.RouteGuard.logError("test-context", new Error("test err"));
<<<<<<< HEAD
    expect(console.error).toHaveBeenCalledWith("[RouteGuard:test-context]", "test err");
=======
    expect(console.error).toHaveBeenCalledWith(
      "[RouteGuard:test-context]",
      "test err",
    );
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
  });

  /* ---- animations ---- */

  describe("animations", () => {
    test("fadeIn returns a promise", () => {
      const el = document.createElement("div");
      const result = window.RouteGuard.animations.fadeIn(el, 10);
      expect(result).toBeInstanceOf(Promise);
    });

    test("fadeOut returns a promise", () => {
      const el = document.createElement("div");
      const result = window.RouteGuard.animations.fadeOut(el, 10);
      expect(result).toBeInstanceOf(Promise);
    });

    test("fadeIn with null returns resolved promise", async () => {
      const result = await window.RouteGuard.animations.fadeIn(null, 10);
      expect(result).toBeUndefined();
    });

    test("slideDown and slideUp return promises", () => {
      const el = document.createElement("div");
      document.body.appendChild(el);
<<<<<<< HEAD
      expect(window.RouteGuard.animations.slideDown(el, 10)).toBeInstanceOf(Promise);
      expect(window.RouteGuard.animations.slideUp(el, 10)).toBeInstanceOf(Promise);
=======
      expect(window.RouteGuard.animations.slideDown(el, 10)).toBeInstanceOf(
        Promise,
      );
      expect(window.RouteGuard.animations.slideUp(el, 10)).toBeInstanceOf(
        Promise,
      );
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
    });
  });

  /* ---- exposed constants ---- */

  test("exposes expected constants", () => {
    expect(window.RouteGuard.TOAST_CONTAINER_ID).toBe("np-toast-container");
    expect(window.RouteGuard.DATA_GUARD_MSG).toBe("data-guard-msg");
    expect(window.RouteGuard.DATA_SV_LOCALIZED).toBe("data-sv-localized");
  });

  /* ---- guardOnChange ---- */

  test("guardOnChange attaches change listener", () => {
    const input = document.createElement("input");
    input.id = "change-test";
    document.body.appendChild(input);

    const cb = jest.fn();
    window.RouteGuard.guardOnChange("change-test", cb);
    expect(input.getAttribute("data-change-listener")).toBe("true");

    // Fire change event
    input.dispatchEvent(new Event("change"));
    expect(cb).toHaveBeenCalledTimes(1);
  });

  test("guardOnChange does not double-attach", () => {
    const input = document.createElement("input");
    input.id = "change-test-2";
    document.body.appendChild(input);

    const cb = jest.fn();
    window.RouteGuard.guardOnChange("change-test-2", cb);
    window.RouteGuard.guardOnChange("change-test-2", cb); // second call

    input.dispatchEvent(new Event("change"));
    expect(cb).toHaveBeenCalledTimes(1); // only once
  });
});
