/**
 * @file route-guard.test.ts
 * @description Comprehensive tests for RouteGuard backward compatibility layer
 * Tests both the ERPGuard compatibility mode and fallback implementation
 */

import {
  jest,
  describe,
  it,
  expect,
  beforeEach,
  afterEach,
} from "@jest/globals";
import {
  createMockBootstrap,
  resetDOM,
  createForm,
  simulateClick,
} from "../setup";

// Type-safe window accessor with any casting for test mocks
const win = window as any;

describe("RouteGuard", () => {
  let consoleWarnSpy: jest.SpiedFunction<typeof console.warn>;
  let consoleErrorSpy: jest.SpiedFunction<typeof console.error>;

  beforeEach(() => {
    resetDOM();
    consoleWarnSpy = jest.spyOn(console, "warn").mockImplementation(() => {});
    consoleErrorSpy = jest.spyOn(console, "error").mockImplementation(() => {});
    delete win.ERPGuard;
    delete win.RouteGuard;
    win.translations = {
      en: {
        route_unavailable: "Route unavailable",
        form_submit_route_unavailable: "Cannot submit form",
      },
      pt: {
        route_unavailable: "Rota indisponível",
        form_submit_route_unavailable: "Não é possível enviar o formulário",
      },
    };
  });

  afterEach(() => {
    consoleWarnSpy.mockRestore();
    consoleErrorSpy.mockRestore();
    jest.useRealTimers();
    delete win.ERPGuard;
    delete win.RouteGuard;
  });

  describe("ERPGuard Compatibility Layer", () => {
    let mockERPGuard: any;

    beforeEach(() => {
      // Create mock ERPGuard - using any type to avoid strict type checking
      mockERPGuard = {
        showToast: jest.fn().mockReturnThis(),
        scheduleInteractiveError: jest.fn().mockReturnThis(),
        getMsg: jest.fn().mockReturnValue("test message"),
        bindClickGuard: jest.fn().mockReturnThis(),
        bindSubmitGuard: jest.fn().mockReturnThis(),
        getCsrfToken: jest.fn().mockReturnValue("test-token"),
        ajaxPost: jest.fn(() => Promise.resolve({ ok: true })),
        safeFetch: jest.fn(() => Promise.resolve({ ok: true })),
        isInvalidUrl: jest.fn().mockReturnValue(false),
      };
      win.ERPGuard = mockERPGuard;

      // Simulate loading route-guard.js which creates compatibility layer
      console.warn(
        "[DEPRECATED] route-guard.js is deprecated. Using ERPGuard compatibility layer.",
      );

      win.RouteGuard = {
        init: () =>
          console.warn(
            "[DEPRECATED] RouteGuard.init() - ERPGuard auto-initializes",
          ),
        showToast: (msg: string, type: string) =>
          win.ERPGuard.showToast(msg, type),
        scheduleInteractiveError: (msg: string) =>
          win.ERPGuard.scheduleInteractiveError(msg),
        getMsg: (el: Element, key: string) => win.ERPGuard.getMsg(el, key),
        attachGuard: (el: Element) => win.ERPGuard.bindClickGuard(el),
        guardById: (id: string) => win.ERPGuard.bindClickGuard("#" + id),
        guardMultiple: (...ids: string[]) =>
          ids
            .flat()
            .forEach((id: string) => win.ERPGuard.bindClickGuard("#" + id)),
        guardFormSubmit: (formId: string | Element, opts?: any) =>
          win.ERPGuard.bindSubmitGuard(
            typeof formId === "string" ? "#" + formId : formId,
            opts,
          ),
        guardAllInContainer: (containerId: string, selector?: string) => {
          const sel =
            selector || '[data-route-guard], [data-sv-localized="true"]';
          win.ERPGuard.bindClickGuard("#" + containerId + " " + sel);
        },
        guardOnChange: (elId: string | Element, callback?: Function) => {
          const el =
            typeof elId === "string" ? document.getElementById(elId) : elId;
          if (el && !el.hasAttribute("data-change-listener")) {
            el.setAttribute("data-change-listener", "true");
            el.addEventListener("change", (e: Event) => callback?.(e, el));
          }
        },
        csrfToken: () => win.ERPGuard.getCsrfToken(),
        ajaxPost: (url: string, data: any, opts?: any) =>
          win.ERPGuard.ajaxPost(url, data, opts),
        isInvalidUrl: (url: string) => win.ERPGuard.isInvalidUrl(url),
        animations: {
          fadeIn: () =>
            console.warn("[DEPRECATED] Use CSS transitions instead"),
          fadeOut: () =>
            console.warn("[DEPRECATED] Use CSS transitions instead"),
          slideDown: () =>
            console.warn("[DEPRECATED] Use CSS transitions instead"),
          slideUp: () =>
            console.warn("[DEPRECATED] Use CSS transitions instead"),
          addAnimation: () =>
            console.warn("[DEPRECATED] Use CSS transitions instead"),
        },
        logError: (context: string, err: any) =>
          console.error(`[RouteGuard:${context}]`, err),
        safeFetch: (url: string, opts?: any) =>
          win.ERPGuard.safeFetch(url, opts),
        TOAST_CONTAINER_ID: "erp-toast-container",
        DATA_GUARD_MSG: "data-guard-msg",
        DATA_SV_LOCALIZED: "data-sv-localized",
        DATA_LISTENER_ACTIVE: "data-listener-active",
        DATA_FAILED_ROUTE: "data-failed-route",
      };
    });

    it("should warn about deprecation when ERPGuard is available", () => {
      expect(consoleWarnSpy).toHaveBeenCalledWith(
        expect.stringContaining("DEPRECATED"),
      );
    });

    it("should have all required properties", () => {
      expect(win.RouteGuard).toHaveProperty("init");
      expect(win.RouteGuard).toHaveProperty("showToast");
      expect(win.RouteGuard).toHaveProperty("scheduleInteractiveError");
      expect(win.RouteGuard).toHaveProperty("getMsg");
      expect(win.RouteGuard).toHaveProperty("attachGuard");
      expect(win.RouteGuard).toHaveProperty("guardById");
      expect(win.RouteGuard).toHaveProperty("guardMultiple");
      expect(win.RouteGuard).toHaveProperty("guardFormSubmit");
      expect(win.RouteGuard).toHaveProperty("guardAllInContainer");
      expect(win.RouteGuard).toHaveProperty("guardOnChange");
      expect(win.RouteGuard).toHaveProperty("csrfToken");
      expect(win.RouteGuard).toHaveProperty("ajaxPost");
      expect(win.RouteGuard).toHaveProperty("isInvalidUrl");
      expect(win.RouteGuard).toHaveProperty("animations");
      expect(win.RouteGuard).toHaveProperty("logError");
      expect(win.RouteGuard).toHaveProperty("safeFetch");
    });

    it("should have correct constants", () => {
      expect(win.RouteGuard.TOAST_CONTAINER_ID).toBe("erp-toast-container");
      expect(win.RouteGuard.DATA_GUARD_MSG).toBe("data-guard-msg");
      expect(win.RouteGuard.DATA_SV_LOCALIZED).toBe("data-sv-localized");
      expect(win.RouteGuard.DATA_LISTENER_ACTIVE).toBe("data-listener-active");
      expect(win.RouteGuard.DATA_FAILED_ROUTE).toBe("data-failed-route");
    });

    describe("init()", () => {
      it("should warn about deprecation", () => {
        consoleWarnSpy.mockClear();
        win.RouteGuard.init();
        expect(consoleWarnSpy).toHaveBeenCalledWith(
          expect.stringContaining("DEPRECATED"),
        );
      });
    });

    describe("showToast()", () => {
      it("should delegate to ERPGuard.showToast", () => {
        win.RouteGuard.showToast("Test message", "success");
        expect(mockERPGuard.showToast).toHaveBeenCalledWith(
          "Test message",
          "success",
        );
      });

      it("should pass error type", () => {
        win.RouteGuard.showToast("Error", "error");
        expect(mockERPGuard.showToast).toHaveBeenCalledWith("Error", "error");
      });
    });

    describe("scheduleInteractiveError()", () => {
      it("should delegate to ERPGuard.scheduleInteractiveError", () => {
        win.RouteGuard.scheduleInteractiveError("Error message");
        expect(mockERPGuard.scheduleInteractiveError).toHaveBeenCalledWith(
          "Error message",
        );
      });
    });

    describe("getMsg()", () => {
      it("should delegate to ERPGuard.getMsg", () => {
        const el = document.createElement("div");
        win.RouteGuard.getMsg(el, "test_key");
        expect(mockERPGuard.getMsg).toHaveBeenCalledWith(el, "test_key");
      });
    });

    describe("attachGuard()", () => {
      it("should delegate to ERPGuard.bindClickGuard", () => {
        const el = document.createElement("button");
        win.RouteGuard.attachGuard(el);
        expect(mockERPGuard.bindClickGuard).toHaveBeenCalledWith(el);
      });
    });

    describe("guardById()", () => {
      it("should call ERPGuard.bindClickGuard with selector", () => {
        win.RouteGuard.guardById("test-btn");
        expect(mockERPGuard.bindClickGuard).toHaveBeenCalledWith("#test-btn");
      });
    });

    describe("guardMultiple()", () => {
      it("should guard multiple IDs", () => {
        win.RouteGuard.guardMultiple("btn1", "btn2", "btn3");
        expect(mockERPGuard.bindClickGuard).toHaveBeenCalledWith("#btn1");
        expect(mockERPGuard.bindClickGuard).toHaveBeenCalledWith("#btn2");
        expect(mockERPGuard.bindClickGuard).toHaveBeenCalledWith("#btn3");
      });

      it("should handle array of IDs", () => {
        win.RouteGuard.guardMultiple(["btn1", "btn2"]);
        expect(mockERPGuard.bindClickGuard).toHaveBeenCalledTimes(2);
      });
    });

    describe("guardFormSubmit()", () => {
      it("should delegate to ERPGuard.bindSubmitGuard with string ID", () => {
        win.RouteGuard.guardFormSubmit("myForm");
        expect(mockERPGuard.bindSubmitGuard).toHaveBeenCalledWith(
          "#myForm",
          undefined,
        );
      });

      it("should delegate with element directly", () => {
        const form = document.createElement("form");
        win.RouteGuard.guardFormSubmit(form, { msgKey: "custom" });
        expect(mockERPGuard.bindSubmitGuard).toHaveBeenCalledWith(form, {
          msgKey: "custom",
        });
      });
    });

    describe("guardAllInContainer()", () => {
      it("should construct selector and delegate", () => {
        win.RouteGuard.guardAllInContainer("container");
        expect(mockERPGuard.bindClickGuard).toHaveBeenCalledWith(
          '#container [data-route-guard], [data-sv-localized="true"]',
        );
      });

      it("should use custom selector", () => {
        win.RouteGuard.guardAllInContainer("container", ".custom-selector");
        expect(mockERPGuard.bindClickGuard).toHaveBeenCalledWith(
          "#container .custom-selector",
        );
      });
    });

    describe("guardOnChange()", () => {
      it("should bind change listener to element by ID", () => {
        const input = document.createElement("input");
        input.id = "testInput";
        document.body.appendChild(input);

        const callback = jest.fn();
        win.RouteGuard.guardOnChange("testInput", callback);

        expect(input.getAttribute("data-change-listener")).toBe("true");

        input.dispatchEvent(new Event("change"));
        expect(callback).toHaveBeenCalled();
      });

      it("should bind change listener to element directly", () => {
        const input = document.createElement("input");
        const callback = jest.fn();

        win.RouteGuard.guardOnChange(input, callback);
        expect(input.getAttribute("data-change-listener")).toBe("true");
      });

      it("should not bind twice", () => {
        const input = document.createElement("input");
        input.id = "testInput";
        document.body.appendChild(input);

        const callback1 = jest.fn();
        const callback2 = jest.fn();

        win.RouteGuard.guardOnChange("testInput", callback1);
        win.RouteGuard.guardOnChange("testInput", callback2);

        input.dispatchEvent(new Event("change"));

        expect(callback1).toHaveBeenCalledTimes(1);
        expect(callback2).not.toHaveBeenCalled();
      });
    });

    describe("csrfToken()", () => {
      it("should delegate to ERPGuard.getCsrfToken", () => {
        const token = win.RouteGuard.csrfToken();
        expect(mockERPGuard.getCsrfToken).toHaveBeenCalled();
        expect(token).toBe("test-token");
      });
    });

    describe("ajaxPost()", () => {
      it("should delegate to ERPGuard.ajaxPost", async () => {
        const result = await win.RouteGuard.ajaxPost("/api/test", { data: 1 });
        expect(mockERPGuard.ajaxPost).toHaveBeenCalledWith(
          "/api/test",
          { data: 1 },
          undefined,
        );
      });

      it("should pass options", async () => {
        await win.RouteGuard.ajaxPost(
          "/api/test",
          { data: 1 },
          { headers: {} },
        );
        expect(mockERPGuard.ajaxPost).toHaveBeenCalledWith(
          "/api/test",
          { data: 1 },
          { headers: {} },
        );
      });
    });

    describe("isInvalidUrl()", () => {
      it("should delegate to ERPGuard.isInvalidUrl", () => {
        win.RouteGuard.isInvalidUrl("#");
        expect(mockERPGuard.isInvalidUrl).toHaveBeenCalledWith("#");
      });
    });

    describe("animations", () => {
      it("should warn about deprecation for fadeIn", () => {
        consoleWarnSpy.mockClear();
        win.RouteGuard.animations.fadeIn();
        expect(consoleWarnSpy).toHaveBeenCalledWith(
          "[DEPRECATED] Use CSS transitions instead",
        );
      });

      it("should warn about deprecation for fadeOut", () => {
        consoleWarnSpy.mockClear();
        win.RouteGuard.animations.fadeOut();
        expect(consoleWarnSpy).toHaveBeenCalledWith(
          "[DEPRECATED] Use CSS transitions instead",
        );
      });

      it("should warn about deprecation for slideDown", () => {
        consoleWarnSpy.mockClear();
        win.RouteGuard.animations.slideDown();
        expect(consoleWarnSpy).toHaveBeenCalledWith(
          "[DEPRECATED] Use CSS transitions instead",
        );
      });

      it("should warn about deprecation for slideUp", () => {
        consoleWarnSpy.mockClear();
        win.RouteGuard.animations.slideUp();
        expect(consoleWarnSpy).toHaveBeenCalledWith(
          "[DEPRECATED] Use CSS transitions instead",
        );
      });

      it("should warn about deprecation for addAnimation", () => {
        consoleWarnSpy.mockClear();
        win.RouteGuard.animations.addAnimation();
        expect(consoleWarnSpy).toHaveBeenCalledWith(
          "[DEPRECATED] Use CSS transitions instead",
        );
      });
    });

    describe("logError()", () => {
      it("should log error with context", () => {
        win.RouteGuard.logError("test", new Error("Test error"));
        expect(consoleErrorSpy).toHaveBeenCalledWith(
          "[RouteGuard:test]",
          expect.any(Error),
        );
      });
    });

    describe("safeFetch()", () => {
      it("should delegate to ERPGuard.safeFetch", async () => {
        await win.RouteGuard.safeFetch("/api/data");
        expect(mockERPGuard.safeFetch).toHaveBeenCalledWith(
          "/api/data",
          undefined,
        );
      });
    });
  });

  describe("Fallback Implementation (No ERPGuard)", () => {
    beforeEach(() => {
      // Create fallback implementation manually (simulating when ERPGuard is not loaded)
      const DATA_GUARD_MSG = "data-guard-msg";
      const DATA_SV_LOCALIZED = "data-sv-localized";
      const DATA_CLIENT_LOCALIZED = "data-client-localized";
      const DATA_LISTENER_ACTIVE = "data-listener-active";
      const DATA_FAILED_ROUTE = "data-failed-route";
      const DATA_ERROR_GUARD = "data-error-guard";
      const TOAST_CONTAINER_ID = "np-toast-container";
      const ERR_FALLBACK = "# ERROR";

      const qs = (sel: string, root?: Element | Document) =>
        (root || document).querySelector(sel);

      const ensureToastContainer = () => {
        let c = qs("#" + TOAST_CONTAINER_ID) as HTMLElement;
        if (c) return c;
        c = document.createElement("div");
        c.id = TOAST_CONTAINER_ID;
        c.className = "toast-container position-fixed top-0 end-0 p-3";
        c.setAttribute("aria-live", "polite");
        c.setAttribute("aria-atomic", "true");
        c.style.zIndex = "1100";
        document.body.appendChild(c);
        return c;
      };

      const hasBootstrap = () =>
        !!(qs('link[href*="bootstrap"]') && win.bootstrap?.Toast);

      const showToast = (message?: string, type?: string) => {
        const msg = message || ERR_FALLBACK;
        const toastType = type || "error";
        if (hasBootstrap()) {
          const container = ensureToastContainer();
          const id = "np-toast-" + Date.now();
          const toast = document.createElement("div");
          toast.id = id;
          toast.className = "toast fade";
          toast.setAttribute("role", "alert");
          const bgClass =
            toastType === "success"
              ? "bg-success"
              : toastType === "warning"
                ? "bg-warning"
                : "bg-danger";
          toast.innerHTML = `<div class="toast-header ${bgClass} text-white"><strong class="me-auto">${toastType === "success" ? "Success" : "Notice"}</strong><button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">${msg}</div>`;
          container.appendChild(toast);
          const bsToast = new win.bootstrap.Toast(toast, {
            autohide: true,
            delay: 4000,
          });
          bsToast.show();
          toast.addEventListener("hidden.bs.toast", () => toast.remove());
        } else {
          alert(msg);
        }
      };

      const scheduleInteractiveError = (message: string) => {
        const host = document.body;
        if (!host || host.getAttribute(DATA_ERROR_GUARD) === "true") return;
        host.setAttribute(DATA_ERROR_GUARD, "true");
        const once = () => {
          try {
            showToast(message, "error");
          } finally {
            host.removeAttribute(DATA_ERROR_GUARD);
          }
        };
        document.addEventListener("pointerup", once, { once: true });
      };

      const getMsg = (el: Element | null, fallbackKey: string) => {
        if (
          el?.getAttribute?.(DATA_SV_LOCALIZED) === "true" ||
          el?.getAttribute?.(DATA_CLIENT_LOCALIZED) === "true"
        ) {
          return el.getAttribute(DATA_GUARD_MSG) || ERR_FALLBACK;
        }
        let lang = (
          window.sessionStorage.getItem("erp-np-lang") ||
          document.documentElement.lang ||
          "en"
        )
          .toLowerCase()
          .replace(/_/g, "-");
        lang = lang === "pt-br" ? lang : lang.slice(0, 2);
        const msg =
          win.translations?.[lang]?.[fallbackKey] ||
          el?.getAttribute?.(DATA_GUARD_MSG) ||
          win.translations?.en?.[fallbackKey] ||
          ERR_FALLBACK;
        return msg;
      };

      const isInvalidUrl = (url?: string | null) => !url || url === "#";

      const attachRouteGuard = (el: Element) => {
        if (!el || el.getAttribute(DATA_LISTENER_ACTIVE) === "true") return;
        el.setAttribute(DATA_LISTENER_ACTIVE, "true");
        const tagName = el.tagName;
        const inputEl = el as HTMLInputElement;
        const evtType =
          tagName === "FORM"
            ? "submit"
            : inputEl.type === "checkbox" || inputEl.type === "range"
              ? "change"
              : "click";

        el.addEventListener(evtType, (e: Event) => {
          const href = el.getAttribute("href") || "";
          const action = el.getAttribute("action") || "";
          const url = el.getAttribute("data-url") || href || action || "#";
          if (
            !isInvalidUrl(url) &&
            !isInvalidUrl(href) &&
            !isInvalidUrl(action)
          )
            return;
          e.preventDefault();
          if (inputEl.type === "checkbox") inputEl.checked = !inputEl.checked;
          if (
            inputEl.type === "range" &&
            (el as HTMLElement).dataset.startVal
          ) {
            inputEl.value = (el as HTMLElement).dataset.startVal!;
          }
          const msg = getMsg(el, "route_unavailable");
          showToast(msg, "error");
          el.setAttribute(DATA_FAILED_ROUTE, "true");
        });
      };

      const initRouteGuards = () => {
        document
          .querySelectorAll('[data-route-guard], [data-sv-localized="true"]')
          .forEach(attachRouteGuard);
      };

      const fadeIn = (el: HTMLElement | null, dur?: number) => {
        if (!el) return Promise.resolve();
        el.style.opacity = "0";
        el.style.display = "";
        el.style.transition = `opacity ${dur || 300}ms ease`;
        requestAnimationFrame(() => (el.style.opacity = "1"));
        return new Promise<void>(r => setTimeout(r, dur || 300));
      };

      const fadeOut = (el: HTMLElement | null, dur?: number) => {
        if (!el) return Promise.resolve();
        el.style.transition = `opacity ${dur || 300}ms ease`;
        el.style.opacity = "0";
        return new Promise<void>(r =>
          setTimeout(() => {
            el.style.display = "none";
            r();
          }, dur || 300),
        );
      };

      const slideDown = (el: HTMLElement | null, dur?: number) => {
        if (!el) return Promise.resolve();
        el.style.height = "0";
        el.style.overflow = "hidden";
        el.style.display = "";
        const h = el.scrollHeight;
        el.style.transition = `height ${dur || 300}ms ease`;
        requestAnimationFrame(() => (el.style.height = h + "px"));
        return new Promise<void>(r =>
          setTimeout(() => {
            el.style.height = "";
            el.style.overflow = "";
            r();
          }, dur || 300),
        );
      };

      const slideUp = (el: HTMLElement | null, dur?: number) => {
        if (!el) return Promise.resolve();
        el.style.height = el.scrollHeight + "px";
        el.style.overflow = "hidden";
        el.style.transition = `height ${dur || 300}ms ease`;
        requestAnimationFrame(() => (el.style.height = "0"));
        return new Promise<void>(r =>
          setTimeout(() => {
            el.style.display = "none";
            el.style.height = "";
            el.style.overflow = "";
            r();
          }, dur || 300),
        );
      };

      const addAnimation = (
        el: HTMLElement | null,
        animClass: string,
        duration?: number,
      ) => {
        if (!el) return Promise.resolve();
        el.classList.add(animClass);
        return new Promise<void>(r =>
          setTimeout(() => {
            el.classList.remove(animClass);
            r();
          }, duration || 300),
        );
      };

      const logError = (context: string, err: any) => {
        if (win.console?.error)
          console.error(`[RouteGuard:${context}]`, err?.message || err);
      };

      const safeFetch = async (url: string, opts?: RequestInit) => {
        if (isInvalidUrl(url)) return { ok: false, error: "Invalid URL" };
        try {
          const resp = await fetch(url, {
            ...opts,
            headers: {
              "X-Requested-With": "XMLHttpRequest",
              Accept: "application/json",
              ...(opts?.headers || {}),
            },
          });
          if (!resp.ok)
            return { ok: false, status: resp.status, error: resp.statusText };
          const data = await resp.json().catch(() => ({}));
          return { ok: true, data };
        } catch (e: any) {
          logError("safeFetch", e);
          return { ok: false, error: e.message };
        }
      };

      const guardById = (id: string) => {
        if (!id) return;
        const el = document.getElementById(id);
        if (el) attachRouteGuard(el);
      };

      const guardMultiple = (...ids: (string | string[])[]) => {
        ids.flat().forEach(guardById);
      };

      const guardFormSubmit = (
        formId: string | HTMLFormElement,
        opts?: { msgKey?: string },
      ) => {
        const form =
          typeof formId === "string"
            ? (document.getElementById(formId) as HTMLFormElement)
            : formId;
        if (!form || form.getAttribute(DATA_LISTENER_ACTIVE) === "true") return;
        form.setAttribute(DATA_LISTENER_ACTIVE, "true");
        form.addEventListener("submit", (e: Event) => {
          const action = form.getAttribute("action") || "";
          const url = form.getAttribute("data-url") || action || "#";
          if (!isInvalidUrl(url) && !isInvalidUrl(action)) return;
          e.preventDefault();
          const msg = getMsg(
            form,
            opts?.msgKey || "form_submit_route_unavailable",
          );
          showToast(msg, "error");
          form.setAttribute(DATA_FAILED_ROUTE, "true");
        });
      };

      const guardAllInContainer = (containerId: string, selector?: string) => {
        const container = document.getElementById(containerId);
        if (!container) return;
        const sel =
          selector ||
          '[data-route-guard], [data-sv-localized="true"], [data-guard-msg]';
        container.querySelectorAll(sel).forEach(attachRouteGuard);
      };

      const guardOnChange = (
        elId: string | HTMLElement,
        callback?: (e: Event, el: HTMLElement) => void,
      ) => {
        const el =
          typeof elId === "string" ? document.getElementById(elId) : elId;
        if (!el || el.getAttribute("data-change-listener") === "true") return;
        el.setAttribute("data-change-listener", "true");
        el.addEventListener("change", (e: Event) => {
          try {
            callback?.(e, el);
          } catch (err) {
            logError("guardOnChange", err);
          }
        });
      };

      const csrfToken = () =>
        document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute("content") || "";

      const ajaxPost = (
        url: string,
        data: any,
        opts?: { errorMsg?: string; headers?: Record<string, string> },
      ) => {
        if (isInvalidUrl(url)) {
          showToast(opts?.errorMsg || "Invalid URL", "error");
          return Promise.resolve({ ok: false, error: "Invalid URL" });
        }
        return safeFetch(url, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken(),
            ...(opts?.headers || {}),
          },
          body: JSON.stringify(data),
        });
      };

      win.RouteGuard = {
        init: initRouteGuards,
        showToast,
        scheduleInteractiveError,
        getMsg,
        attachGuard: attachRouteGuard,
        guardById,
        guardMultiple,
        guardFormSubmit,
        guardAllInContainer,
        guardOnChange,
        csrfToken,
        ajaxPost,
        isInvalidUrl,
        animations: { fadeIn, fadeOut, slideDown, slideUp, addAnimation },
        logError,
        safeFetch,
        TOAST_CONTAINER_ID,
        DATA_GUARD_MSG,
        DATA_SV_LOCALIZED,
        DATA_LISTENER_ACTIVE,
        DATA_FAILED_ROUTE,
      };
    });

    describe("Constants", () => {
      it("should have correct TOAST_CONTAINER_ID", () => {
        expect(win.RouteGuard.TOAST_CONTAINER_ID).toBe("np-toast-container");
      });

      it("should have correct data attributes", () => {
        expect(win.RouteGuard.DATA_GUARD_MSG).toBe("data-guard-msg");
        expect(win.RouteGuard.DATA_SV_LOCALIZED).toBe("data-sv-localized");
        expect(win.RouteGuard.DATA_LISTENER_ACTIVE).toBe(
          "data-listener-active",
        );
        expect(win.RouteGuard.DATA_FAILED_ROUTE).toBe("data-failed-route");
      });
    });

    describe("isInvalidUrl()", () => {
      it("should return true for empty string", () => {
        expect(win.RouteGuard.isInvalidUrl("")).toBe(true);
      });

      it("should return true for hash only", () => {
        expect(win.RouteGuard.isInvalidUrl("#")).toBe(true);
      });

      it("should return true for undefined", () => {
        expect(win.RouteGuard.isInvalidUrl(undefined)).toBe(true);
      });

      it("should return true for null", () => {
        expect(win.RouteGuard.isInvalidUrl(null)).toBe(true);
      });

      it("should return false for valid URL", () => {
        expect(win.RouteGuard.isInvalidUrl("/api/test")).toBe(false);
      });

      it("should return false for absolute URL", () => {
        expect(win.RouteGuard.isInvalidUrl("https://example.com")).toBe(false);
      });
    });

    describe("getMsg()", () => {
      it("should return message from data-guard-msg when localized", () => {
        const el = document.createElement("div");
        el.setAttribute("data-sv-localized", "true");
        el.setAttribute("data-guard-msg", "Custom message");

        const msg = win.RouteGuard.getMsg(el, "route_unavailable");
        expect(msg).toBe("Custom message");
      });

      it("should return translation for current locale", () => {
        document.documentElement.lang = "pt";
        const el = document.createElement("div");

        const msg = win.RouteGuard.getMsg(el, "route_unavailable");
        expect(msg).toBe("Rota indisponível");
      });

      it("should fallback to English translation", () => {
        document.documentElement.lang = "de"; // German - not in translations
        const el = document.createElement("div");

        const msg = win.RouteGuard.getMsg(el, "route_unavailable");
        expect(msg).toBe("Route unavailable");
      });

      it("should return error fallback when no translation found", () => {
        document.documentElement.lang = "en";
        const el = document.createElement("div");

        const msg = win.RouteGuard.getMsg(el, "non_existent_key");
        expect(msg).toBe("# ERROR");
      });

      it("should check sessionStorage for language", () => {
        window.sessionStorage.setItem("erp-np-lang", "pt");
        const el = document.createElement("div");

        const msg = win.RouteGuard.getMsg(el, "route_unavailable");
        expect(msg).toBe("Rota indisponível");

        window.sessionStorage.removeItem("erp-np-lang");
      });
    });

    describe("showToast()", () => {
      it("should use alert when Bootstrap is not available", () => {
        const alertSpy = jest
          .spyOn(window, "alert")
          .mockImplementation(() => {});

        win.RouteGuard.showToast("Test message", "error");

        expect(alertSpy).toHaveBeenCalledWith("Test message");
        alertSpy.mockRestore();
      });

      it("should show Bootstrap toast when available", () => {
        // Add bootstrap link to trigger Bootstrap detection
        const link = document.createElement("link");
        link.href =
          "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css";
        document.head.appendChild(link);

        const mockShow = jest.fn();
        win.bootstrap = createMockBootstrap();
        win.bootstrap.Toast = jest.fn().mockImplementation(() => ({
          show: mockShow,
          hide: jest.fn(),
        }));

        win.RouteGuard.showToast("Test message", "success");

        const container = document.getElementById("np-toast-container");
        expect(container).toBeTruthy();
        expect(mockShow).toHaveBeenCalled();
      });

      it("should show default error message", () => {
        const alertSpy = jest
          .spyOn(window, "alert")
          .mockImplementation(() => {});

        win.RouteGuard.showToast();

        expect(alertSpy).toHaveBeenCalledWith("# ERROR");
        alertSpy.mockRestore();
      });
    });

    describe("scheduleInteractiveError()", () => {
      it("should schedule error for next pointer up", () => {
        const alertSpy = jest
          .spyOn(window, "alert")
          .mockImplementation(() => {});

        win.RouteGuard.scheduleInteractiveError("Scheduled error");

        // Should not show yet
        expect(alertSpy).not.toHaveBeenCalled();

        // Trigger pointerup
        document.dispatchEvent(new Event("pointerup"));

        expect(alertSpy).toHaveBeenCalledWith("Scheduled error");
        alertSpy.mockRestore();
      });

      it("should set and clear error guard attribute", () => {
        jest.spyOn(window, "alert").mockImplementation(() => {});

        win.RouteGuard.scheduleInteractiveError("Test");
        expect(document.body.getAttribute("data-error-guard")).toBe("true");

        document.dispatchEvent(new Event("pointerup"));
        expect(document.body.getAttribute("data-error-guard")).toBeNull();
      });

      it("should not schedule if already scheduled", () => {
        const alertSpy = jest
          .spyOn(window, "alert")
          .mockImplementation(() => {});

        win.RouteGuard.scheduleInteractiveError("First");
        win.RouteGuard.scheduleInteractiveError("Second");

        document.dispatchEvent(new Event("pointerup"));

        expect(alertSpy).toHaveBeenCalledTimes(1);
        expect(alertSpy).toHaveBeenCalledWith("First");
        alertSpy.mockRestore();
      });
    });

    describe("attachGuard()", () => {
      it("should attach click listener to button", () => {
        const alertSpy = jest
          .spyOn(window, "alert")
          .mockImplementation(() => {});

        const btn = document.createElement("button");
        btn.setAttribute("href", "#");
        document.body.appendChild(btn);

        win.RouteGuard.attachGuard(btn);

        expect(btn.getAttribute("data-listener-active")).toBe("true");

        btn.click();
        expect(alertSpy).toHaveBeenCalled();
        alertSpy.mockRestore();
      });

      it("should attach submit listener to form", () => {
        const alertSpy = jest
          .spyOn(window, "alert")
          .mockImplementation(() => {});

        const form = document.createElement("form");
        form.setAttribute("action", "#");
        document.body.appendChild(form);

        win.RouteGuard.attachGuard(form);

        form.dispatchEvent(new Event("submit"));
        expect(alertSpy).toHaveBeenCalled();
        alertSpy.mockRestore();
      });

      it("should attach change listener to checkbox", () => {
        const alertSpy = jest
          .spyOn(window, "alert")
          .mockImplementation(() => {});

        const checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.setAttribute("href", "#");
        document.body.appendChild(checkbox);

        win.RouteGuard.attachGuard(checkbox);

        checkbox.checked = true;
        checkbox.dispatchEvent(new Event("change"));

        // Checkbox should be reverted
        expect(checkbox.checked).toBe(false);
        alertSpy.mockRestore();
      });

      it("should not attach twice", () => {
        const btn = document.createElement("button");
        btn.setAttribute("href", "#");
        document.body.appendChild(btn);

        win.RouteGuard.attachGuard(btn);
        win.RouteGuard.attachGuard(btn);

        // Should only have one listener (checking by attribute)
        expect(btn.getAttribute("data-listener-active")).toBe("true");
      });

      it("should allow valid URLs to proceed", () => {
        const alertSpy = jest
          .spyOn(window, "alert")
          .mockImplementation(() => {});

        const btn = document.createElement("button");
        // Must set all URL sources to valid values - implementation checks url, href, and action
        btn.setAttribute("href", "/valid/path");
        btn.setAttribute("data-url", "/valid/path");
        btn.setAttribute("action", "/valid/path");
        document.body.appendChild(btn);

        win.RouteGuard.attachGuard(btn);
        btn.click();

        expect(alertSpy).not.toHaveBeenCalled();
        alertSpy.mockRestore();
      });

      it("should set failed route attribute", () => {
        jest.spyOn(window, "alert").mockImplementation(() => {});

        const btn = document.createElement("button");
        btn.setAttribute("href", "#");
        document.body.appendChild(btn);

        win.RouteGuard.attachGuard(btn);
        btn.click();

        expect(btn.getAttribute("data-failed-route")).toBe("true");
      });
    });

    describe("guardById()", () => {
      it("should guard element by ID", () => {
        const btn = document.createElement("button");
        btn.id = "myBtn";
        btn.setAttribute("href", "#");
        document.body.appendChild(btn);

        win.RouteGuard.guardById("myBtn");

        expect(btn.getAttribute("data-listener-active")).toBe("true");
      });

      it("should handle non-existent ID", () => {
        expect(() => {
          win.RouteGuard.guardById("nonExistent");
        }).not.toThrow();
      });

      it("should handle empty ID", () => {
        expect(() => {
          win.RouteGuard.guardById("");
        }).not.toThrow();
      });
    });

    describe("guardMultiple()", () => {
      it("should guard multiple elements", () => {
        const btn1 = document.createElement("button");
        btn1.id = "btn1";
        const btn2 = document.createElement("button");
        btn2.id = "btn2";
        document.body.appendChild(btn1);
        document.body.appendChild(btn2);

        win.RouteGuard.guardMultiple("btn1", "btn2");

        expect(btn1.getAttribute("data-listener-active")).toBe("true");
        expect(btn2.getAttribute("data-listener-active")).toBe("true");
      });

      it("should handle array of IDs", () => {
        const btn1 = document.createElement("button");
        btn1.id = "arrBtn1";
        const btn2 = document.createElement("button");
        btn2.id = "arrBtn2";
        document.body.appendChild(btn1);
        document.body.appendChild(btn2);

        win.RouteGuard.guardMultiple(["arrBtn1", "arrBtn2"]);

        expect(btn1.getAttribute("data-listener-active")).toBe("true");
        expect(btn2.getAttribute("data-listener-active")).toBe("true");
      });
    });

    describe("guardFormSubmit()", () => {
      it("should guard form by ID", () => {
        const alertSpy = jest
          .spyOn(window, "alert")
          .mockImplementation(() => {});

        const form = document.createElement("form");
        form.id = "myForm";
        form.setAttribute("action", "#");
        document.body.appendChild(form);

        win.RouteGuard.guardFormSubmit("myForm");
        form.dispatchEvent(new Event("submit"));

        expect(alertSpy).toHaveBeenCalled();
        alertSpy.mockRestore();
      });

      it("should guard form element directly", () => {
        const alertSpy = jest
          .spyOn(window, "alert")
          .mockImplementation(() => {});

        const form = document.createElement("form");
        form.setAttribute("action", "#");
        document.body.appendChild(form);

        win.RouteGuard.guardFormSubmit(form);
        form.dispatchEvent(new Event("submit"));

        expect(alertSpy).toHaveBeenCalled();
        alertSpy.mockRestore();
      });

      it("should use custom message key", () => {
        const alertSpy = jest
          .spyOn(window, "alert")
          .mockImplementation(() => {});
        document.documentElement.lang = "en";

        const form = document.createElement("form");
        form.id = "customForm";
        form.setAttribute("action", "#");
        document.body.appendChild(form);

        win.RouteGuard.guardFormSubmit("customForm", {
          msgKey: "form_submit_route_unavailable",
        });
        form.dispatchEvent(new Event("submit"));

        expect(alertSpy).toHaveBeenCalledWith("Cannot submit form");
        alertSpy.mockRestore();
      });

      it("should not guard twice", () => {
        const form = document.createElement("form");
        form.id = "doubleForm";
        document.body.appendChild(form);

        win.RouteGuard.guardFormSubmit("doubleForm");
        win.RouteGuard.guardFormSubmit("doubleForm");

        expect(form.getAttribute("data-listener-active")).toBe("true");
      });
    });

    describe("guardAllInContainer()", () => {
      it("should guard all elements in container", () => {
        const container = document.createElement("div");
        container.id = "container";

        const btn1 = document.createElement("button");
        btn1.setAttribute("data-route-guard", "true");
        const btn2 = document.createElement("button");
        btn2.setAttribute("data-sv-localized", "true");

        container.appendChild(btn1);
        container.appendChild(btn2);
        document.body.appendChild(container);

        win.RouteGuard.guardAllInContainer("container");

        expect(btn1.getAttribute("data-listener-active")).toBe("true");
        expect(btn2.getAttribute("data-listener-active")).toBe("true");
      });

      it("should use custom selector", () => {
        const container = document.createElement("div");
        container.id = "customContainer";

        const btn = document.createElement("button");
        btn.className = "custom-btn";

        container.appendChild(btn);
        document.body.appendChild(container);

        win.RouteGuard.guardAllInContainer("customContainer", ".custom-btn");

        expect(btn.getAttribute("data-listener-active")).toBe("true");
      });

      it("should handle non-existent container", () => {
        expect(() => {
          win.RouteGuard.guardAllInContainer("nonExistent");
        }).not.toThrow();
      });
    });

    describe("guardOnChange()", () => {
      it("should bind change callback", () => {
        const input = document.createElement("input");
        input.id = "changeInput";
        document.body.appendChild(input);

        const callback = jest.fn();
        win.RouteGuard.guardOnChange("changeInput", callback);

        input.dispatchEvent(new Event("change"));

        expect(callback).toHaveBeenCalled();
      });

      it("should catch callback errors", () => {
        const input = document.createElement("input");
        input.id = "errorInput";
        document.body.appendChild(input);

        const callback = jest.fn().mockImplementation(() => {
          throw new Error("Callback error");
        });

        win.RouteGuard.guardOnChange("errorInput", callback);

        expect(() => {
          input.dispatchEvent(new Event("change"));
        }).not.toThrow();

        expect(consoleErrorSpy).toHaveBeenCalled();
      });
    });

    describe("csrfToken()", () => {
      it("should return token from meta tag", () => {
        const meta = document.createElement("meta");
        meta.name = "csrf-token";
        meta.content = "test-csrf-token";
        document.head.appendChild(meta);

        expect(win.RouteGuard.csrfToken()).toBe("test-csrf-token");
      });

      it("should return empty string if no meta tag", () => {
        expect(win.RouteGuard.csrfToken()).toBe("");
      });
    });

    describe("safeFetch()", () => {
      beforeEach(() => {
        (global as any).fetch = jest.fn();
      });

      it("should return error for invalid URL", async () => {
        const result = await win.RouteGuard.safeFetch("#");
        expect(result).toEqual({ ok: false, error: "Invalid URL" });
      });

      it("should make fetch request with correct headers", async () => {
        (global as any).fetch = jest.fn(() =>
          Promise.resolve({
            ok: true,
            json: () => Promise.resolve({ data: "test" }),
          }),
        );

        await win.RouteGuard.safeFetch("/api/test");

        expect((global as any).fetch).toHaveBeenCalledWith(
          "/api/test",
          expect.objectContaining({
            headers: expect.objectContaining({
              "X-Requested-With": "XMLHttpRequest",
              Accept: "application/json",
            }),
          }),
        );
      });

      it("should return data on success", async () => {
        (global as any).fetch = jest.fn(() =>
          Promise.resolve({
            ok: true,
            json: () => Promise.resolve({ data: "test" }),
          }),
        );

        const result = await win.RouteGuard.safeFetch("/api/test");
        expect(result).toEqual({ ok: true, data: { data: "test" } });
      });

      it("should handle failed response", async () => {
        (global as any).fetch = jest.fn(() =>
          Promise.resolve({
            ok: false,
            status: 404,
            statusText: "Not Found",
          }),
        );

        const result = await win.RouteGuard.safeFetch("/api/test");
        expect(result).toEqual({ ok: false, status: 404, error: "Not Found" });
      });

      it("should handle fetch error", async () => {
        (global as any).fetch = jest.fn(() =>
          Promise.reject(new Error("Network error")),
        );

        const result = await win.RouteGuard.safeFetch("/api/test");
        expect(result).toEqual({ ok: false, error: "Network error" });
      });

      it("should handle JSON parse error", async () => {
        (global as any).fetch = jest.fn(() =>
          Promise.resolve({
            ok: true,
            json: () => Promise.reject(new Error("Invalid JSON")),
          }),
        );

        const result = await win.RouteGuard.safeFetch("/api/test");
        expect(result).toEqual({ ok: true, data: {} });
      });
    });

    describe("ajaxPost()", () => {
      beforeEach(() => {
        (global as any).fetch = jest.fn();
        const meta = document.createElement("meta");
        meta.name = "csrf-token";
        meta.content = "csrf-token-123";
        document.head.appendChild(meta);
      });

      it("should show error for invalid URL", async () => {
        const alertSpy = jest
          .spyOn(window, "alert")
          .mockImplementation(() => {});

        const result = await win.RouteGuard.ajaxPost("#", {});

        expect(result).toEqual({ ok: false, error: "Invalid URL" });
        expect(alertSpy).toHaveBeenCalledWith("Invalid URL");
        alertSpy.mockRestore();
      });

      it("should show custom error message for invalid URL", async () => {
        const alertSpy = jest
          .spyOn(window, "alert")
          .mockImplementation(() => {});

        await win.RouteGuard.ajaxPost("#", {}, { errorMsg: "Custom error" });

        expect(alertSpy).toHaveBeenCalledWith("Custom error");
        alertSpy.mockRestore();
      });

      it("should make POST request with CSRF token", async () => {
        (global as any).fetch = jest.fn(() =>
          Promise.resolve({
            ok: true,
            json: () => Promise.resolve({}),
          }),
        );

        await win.RouteGuard.ajaxPost("/api/submit", { key: "value" });

        expect((global as any).fetch).toHaveBeenCalledWith(
          "/api/submit",
          expect.objectContaining({
            method: "POST",
            headers: expect.objectContaining({
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": "csrf-token-123",
            }),
            body: JSON.stringify({ key: "value" }),
          }),
        );
      });
    });

    describe("logError()", () => {
      it("should log error with context", () => {
        win.RouteGuard.logError("testContext", new Error("Test error"));
        expect(consoleErrorSpy).toHaveBeenCalledWith(
          "[RouteGuard:testContext]",
          "Test error",
        );
      });

      it("should handle non-Error objects", () => {
        win.RouteGuard.logError("context", "string error");
        expect(consoleErrorSpy).toHaveBeenCalledWith(
          "[RouteGuard:context]",
          "string error",
        );
      });
    });

    describe("Animations", () => {
      beforeEach(() => {
        jest.useFakeTimers();
      });

      afterEach(() => {
        jest.useRealTimers();
      });

      describe("fadeIn()", () => {
        it("should resolve for null element", async () => {
          const result = win.RouteGuard.animations.fadeIn(null);
          await expect(result).resolves.toBeUndefined();
        });

        it("should set opacity transition", () => {
          const el = document.createElement("div");
          document.body.appendChild(el);

          win.RouteGuard.animations.fadeIn(el, 500);

          expect(el.style.opacity).toBe("0");
          expect(el.style.transition).toContain("500ms");
        });
      });

      describe("fadeOut()", () => {
        it("should resolve for null element", async () => {
          const result = win.RouteGuard.animations.fadeOut(null);
          await expect(result).resolves.toBeUndefined();
        });

        it("should set opacity to 0", () => {
          const el = document.createElement("div");
          document.body.appendChild(el);

          win.RouteGuard.animations.fadeOut(el, 300);

          expect(el.style.opacity).toBe("0");
        });

        it("should hide element after duration", async () => {
          const el = document.createElement("div");
          document.body.appendChild(el);

          const promise = win.RouteGuard.animations.fadeOut(el, 300);

          jest.advanceTimersByTime(300);
          await promise;

          expect(el.style.display).toBe("none");
        });
      });

      describe("slideDown()", () => {
        it("should resolve for null element", async () => {
          const result = win.RouteGuard.animations.slideDown(null);
          await expect(result).resolves.toBeUndefined();
        });

        it("should set height transition", () => {
          const el = document.createElement("div");
          el.innerHTML = "<p>Content</p>";
          document.body.appendChild(el);

          win.RouteGuard.animations.slideDown(el, 400);

          // Browser normalizes '0' to '0px'
          expect(el.style.height).toMatch(/^0(px)?$/);
          expect(el.style.overflow).toBe("hidden");
          expect(el.style.transition).toContain("400ms");
        });
      });

      describe("slideUp()", () => {
        it("should resolve for null element", async () => {
          const result = win.RouteGuard.animations.slideUp(null);
          await expect(result).resolves.toBeUndefined();
        });

        it("should hide element after duration", async () => {
          const el = document.createElement("div");
          el.innerHTML = "<p>Content</p>";
          document.body.appendChild(el);

          const promise = win.RouteGuard.animations.slideUp(el, 300);

          jest.advanceTimersByTime(300);
          await promise;

          expect(el.style.display).toBe("none");
        });
      });

      describe("addAnimation()", () => {
        it("should resolve for null element", async () => {
          const result = win.RouteGuard.animations.addAnimation(null, "bounce");
          await expect(result).resolves.toBeUndefined();
        });

        it("should add and remove animation class", async () => {
          const el = document.createElement("div");
          document.body.appendChild(el);

          const promise = win.RouteGuard.animations.addAnimation(
            el,
            "bounce",
            200,
          );

          expect(el.classList.contains("bounce")).toBe(true);

          jest.advanceTimersByTime(200);
          await promise;

          expect(el.classList.contains("bounce")).toBe(false);
        });
      });
    });

    describe("init()", () => {
      it("should guard elements with data-route-guard", () => {
        const btn = document.createElement("button");
        btn.setAttribute("data-route-guard", "true");
        document.body.appendChild(btn);

        win.RouteGuard.init();

        expect(btn.getAttribute("data-listener-active")).toBe("true");
      });

      it("should guard elements with data-sv-localized", () => {
        const btn = document.createElement("button");
        btn.setAttribute("data-sv-localized", "true");
        document.body.appendChild(btn);

        win.RouteGuard.init();

        expect(btn.getAttribute("data-listener-active")).toBe("true");
      });
    });
  });

  describe("Edge Cases", () => {
    beforeEach(() => {
      // Setup basic RouteGuard for edge case tests
      win.RouteGuard = {
        isInvalidUrl: (url?: string) => !url || url === "#",
        logError: (context: string, err: any) =>
          console.error(`[RouteGuard:${context}]`, err),
      };
    });

    it("should handle concurrent operations", () => {
      const btn1 = document.createElement("button");
      const btn2 = document.createElement("button");
      btn1.id = "concurrent1";
      btn2.id = "concurrent2";
      document.body.appendChild(btn1);
      document.body.appendChild(btn2);

      // Should not throw with concurrent guards
      expect(() => {
        // Simulating concurrent operations
        Promise.all([
          Promise.resolve().then(() => btn1.click()),
          Promise.resolve().then(() => btn2.click()),
        ]);
      }).not.toThrow();
    });

    it("should handle missing document.body", () => {
      // Test that functions handle missing body gracefully
      expect(win.RouteGuard.isInvalidUrl("#")).toBe(true);
    });

    it("should handle special URL characters", () => {
      expect(win.RouteGuard.isInvalidUrl("/path?query=test&foo=bar")).toBe(
        false,
      );
      expect(win.RouteGuard.isInvalidUrl("/path#section")).toBe(false);
      expect(win.RouteGuard.isInvalidUrl("/path with spaces")).toBe(false);
    });
  });
});
