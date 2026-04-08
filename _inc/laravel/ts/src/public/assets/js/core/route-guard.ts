/**
 * route-guard.ts — Legacy route guard implementation (backward compat).
 *
 * @deprecated Use erp-guard.ts / erp-guard.js instead.
 *
 * If ERPGuard exists, creates a compatibility shim mapping
 * `window.RouteGuard` → `window.ERPGuard`. Otherwise provides
 * a standalone fallback implementation.
 *
 * Mirror of public/assets/js/core/route-guard.js
 * @module core/route-guard
 */

// ToastType extraído de globals.d.ts para o shim de compatibilidade
type ShimToastType = "success" | "error" | "warning" | "info" | "primary" | "secondary" | "dark" | "light";

/* ---------- Types ------------------------------------------------------- */

interface RouteGuardAnimations {
  fadeIn(el: HTMLElement | null, dur?: number): Promise<void>;
  fadeOut(el: HTMLElement | null, dur?: number): Promise<void>;
  slideDown(el: HTMLElement | null, dur?: number): Promise<void>;
  slideUp(el: HTMLElement | null, dur?: number): Promise<void>;
  addAnimation(
    el: HTMLElement | null,
    animClass: string,
    dur?: number,
  ): Promise<void>;
}

interface RouteGuardAPI {
  init(): void;
  showToast(message: string, type?: string): void;
  scheduleInteractiveError(message: string): void;
  getMsg(el: HTMLElement | null, fallbackKey: string): string;
  attachGuard(el: HTMLElement): void;
  guardById(id: string): void;
  guardMultiple(...ids: (string | string[])[]): void;
  guardFormSubmit(
    formId: string | HTMLFormElement,
    opts?: { msgKey?: string },
  ): void;
  guardAllInContainer(containerId: string, selector?: string): void;
  guardOnChange(
    elId: string | HTMLElement,
    callback?: (e: Event, el: HTMLElement) => void,
  ): void;
  csrfToken(): string;
  ajaxPost(
    url: string,
    data: unknown,
    opts?: { errorMsg?: string; headers?: Record<string, string> },
  ): Promise<{ ok: boolean; data?: unknown; status?: number; error?: string }>;
  isInvalidUrl(url: string | null | undefined): boolean;
  animations: RouteGuardAnimations;
  logError(context: string, err: unknown): void;
  safeFetch(
    url: string,
    opts?: RequestInit,
  ): Promise<{ ok: boolean; data?: unknown; status?: number; error?: string }>;
  TOAST_CONTAINER_ID: string;
  DATA_GUARD_MSG: string;
  DATA_SV_LOCALIZED: string;
  DATA_LISTENER_ACTIVE: string;
  DATA_FAILED_ROUTE: string;
}

declare global {
  interface Window {
    RouteGuard?: RouteGuardAPI;
    translations?: Record<string, Record<string, string>>;
  }
}

/* ---------- Logging ----------------------------------------------------- */

const _emitted: Record<string, true> = {};

const isDev = (): boolean =>
  location.hostname === "localhost" || location.hostname === "127.0.0.1";

const devError = (ctx: string, err: unknown): void => {
  if (!isDev()) return;
  const msg = err instanceof Error ? err.message : String(err);
  const key = `${ctx}:${msg}`;
  if (_emitted[key]) return;
  _emitted[key] = true;
  console.error(`[RouteGuard:${ctx}]`, msg);
};

/* ---------- Implementation ---------------------------------------------- */

(function (): void {
  "use strict";

  // Referência local tipada para evitar encadeamento repetido
  const guard = window.ERPGuard;

  /* ── Compatibility layer when ERPGuard is present ─────────────── */

  if (guard) {
    window.RouteGuard = {
      init: (): void => void 0,
      showToast: (msg: string, type?: string): void => {
        // Shim de compatibilidade — tipo string da API legada mapeado para ToastType
        // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
        guard.showToast(msg, type as ShimToastType | undefined);
      },
      scheduleInteractiveError: (msg): void =>
        guard.scheduleInteractiveError(msg),
      getMsg: (_el, key): string => guard.getMsg(key),
      attachGuard: (el): void => guard.bindClickGuard(el),
      guardById: (id): void =>
        guard.bindClickGuard(document.getElementById(id)!),
      guardMultiple: (...ids): void =>
        ids.flat().forEach((id: string) =>
          guard.bindClickGuard(document.getElementById(id)!),
        ),
      guardFormSubmit: (formId, opts): void =>
        guard.bindSubmitGuard(
          typeof formId === "string"
            ? document.querySelector<HTMLFormElement>(
                "#" + formId,
              )!
            : formId,
          null,
          opts as unknown as Record<string, unknown>,
        ),
      guardAllInContainer: (containerId, selector): void => {
        const sel =
          selector || '[data-route-guard], [data-sv-localized="true"]';
        const container = document.getElementById(containerId);
        if (container) {
          container
            .querySelectorAll(sel)
            .forEach((el) => guard.bindClickGuard(el as HTMLElement));
        }
      },
      guardOnChange: (elId, callback): void => {
        const el =
          typeof elId === "string" ? document.getElementById(elId) : elId;
        if (el && !el.hasAttribute("data-change-listener")) {
          el.setAttribute("data-change-listener", "true");
          el.addEventListener("change", (e) => callback?.(e, el));
        }
      },
      csrfToken: (): string => guard.getCsrfToken(),
      ajaxPost: (url, data, opts): Promise<{ ok: boolean; data?: unknown; status?: number; error?: string }> =>
        guard.ajaxPost(url, data, opts) as unknown as Promise<{ ok: boolean; data?: unknown; status?: number; error?: string }>,
      isInvalidUrl: (url): boolean => guard.isInvalidUrl(url ?? ""),
      animations: {
        fadeIn: (): Promise<void> => Promise.resolve(),
        fadeOut: (): Promise<void> => Promise.resolve(),
        slideDown: (): Promise<void> => Promise.resolve(),
        slideUp: (): Promise<void> => Promise.resolve(),
        addAnimation: (): Promise<void> => Promise.resolve(),
      },
      logError: (context, err): void => devError(context, err),
      safeFetch: (url, opts): Promise<{ ok: boolean; data?: unknown; status?: number; error?: string }> =>
        guard.safeFetch(url, opts) as unknown as Promise<{ ok: boolean; data?: unknown; status?: number; error?: string }>,
      TOAST_CONTAINER_ID: "erp-toast-container",
      DATA_GUARD_MSG: "data-guard-msg",
      DATA_SV_LOCALIZED: "data-sv-localized",
      DATA_LISTENER_ACTIVE: "data-listener-active",
      DATA_FAILED_ROUTE: "data-failed-route",
    };
    return;
  }

  /* ── Standalone fallback ─────────────────────────────────────── */

  const DATA_GUARD_MSG = "data-guard-msg",
    DATA_SV_LOCALIZED = "data-sv-localized",
    DATA_CLIENT_LOCALIZED = "data-client-localized",
    DATA_LISTENER_ACTIVE = "data-listener-active",
    DATA_FAILED_ROUTE = "data-failed-route",
    DATA_ERROR_GUARD = "data-error-guard",
    TOAST_CONTAINER_ID = "np-toast-container",
    ERR_FALLBACK = "# ERROR";

  const qs = (sel: string, root?: Document | HTMLElement): HTMLElement | null =>
    (root ?? document).querySelector(sel);

  const ensureToastContainer = (): HTMLElement => {
    let c = qs("#" + TOAST_CONTAINER_ID);
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

  const hasBootstrap = (): boolean =>
    !!(qs('link[href*="bootstrap"]') && (window as unknown as Record<string, Record<string, unknown>>).bootstrap?.Toast);

  const showToast = (message?: string, type?: string): void => {
    const msg = message || ERR_FALLBACK,
      toastType = type || "error";
    if (hasBootstrap()) {
      const container = ensureToastContainer(),
        id = "np-toast-" + Date.now(),
        toast = document.createElement("div");
      toast.id = id;
      toast.className = "toast fade";
      toast.setAttribute("role", "alert");
      toast.setAttribute("aria-live", "assertive");
      toast.setAttribute("aria-atomic", "true");
      const bgClass =
        toastType === "success"
          ? "bg-success"
          : toastType === "warning"
            ? "bg-warning"
            : "bg-danger";
      toast.innerHTML = `<div class="toast-header ${bgClass} text-white"><strong class="me-auto">${toastType === "success" ? "Success" : "Notice"}</strong><button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body">${msg}</div>`;
      container.appendChild(toast);
      const bsToast = new ((window as unknown as Record<string, Record<string, new (el: HTMLElement, opts: Record<string, unknown>) => { show(): void }>>).bootstrap.Toast)(toast, {
        autohide: true,
        delay: 4000,
      });
      bsToast.show();
      toast.addEventListener("hidden.bs.toast", () => toast.remove());
    } else {
      alert(msg);
    }
  };

  const scheduleInteractiveError = (message: string): void => {
    const host = document.body;
    if (!host || host.getAttribute(DATA_ERROR_GUARD) === "true") return;
    host.setAttribute(DATA_ERROR_GUARD, "true");
    const once = (): void => {
      try {
        showToast(message, "error");
      } finally {
        host.removeAttribute(DATA_ERROR_GUARD);
      }
    };
    document.addEventListener("pointerup", once, { once: true });
    const mo = new MutationObserver((_, o) => {
      if (!document.body.contains(host)) {
        document.removeEventListener("pointerup", once);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };

  const getMsg = (el: HTMLElement | null, fallbackKey: string): string => {
    if (
      el?.getAttribute?.(DATA_SV_LOCALIZED) === "true" ||
      el?.getAttribute?.(DATA_CLIENT_LOCALIZED) === "true"
    )
      return el.getAttribute(DATA_GUARD_MSG) || ERR_FALLBACK;
    let lang = (
      window.sessionStorage.getItem("erp-np-lang") ||
      document.documentElement.lang ||
      "en"
    )
      .toLowerCase()
      .replace(/_/g, "-");
    lang = lang === "pt-br" ? lang : lang.slice(0, 2);
    const msg =
      window.translations?.[lang]?.[fallbackKey] ||
      el?.getAttribute?.(DATA_GUARD_MSG) ||
      window.translations?.en?.[fallbackKey] ||
      ERR_FALLBACK;
    if (el && msg !== ERR_FALLBACK) {
      el.setAttribute(DATA_GUARD_MSG, msg);
      el.setAttribute(DATA_CLIENT_LOCALIZED, "true");
    }
    return msg;
  };

  const isInvalidUrl = (url: string | null | undefined): boolean =>
    !url || url === "#";

  const attachRouteGuard = (el: HTMLElement): void => {
    if (!el || el.getAttribute(DATA_LISTENER_ACTIVE) === "true") return;
    el.setAttribute(DATA_LISTENER_ACTIVE, "true");
    const evtType =
      el.tagName === "FORM"
        ? "submit"
        : (el as HTMLInputElement).type === "checkbox" ||
            (el as HTMLInputElement).type === "range"
          ? "change"
          : "click";
    el.addEventListener(evtType, (e: Event) => {
      const isForm = el.tagName === "FORM",
        href = isForm ? null : el.getAttribute("href") || "",
        action = el.getAttribute("action") || "",
        url = el.getAttribute("data-url") || href || action || "#";
      if (isForm) {
        if (!isInvalidUrl(url) && !isInvalidUrl(action)) return;
      } else {
        if (!isInvalidUrl(url) && !isInvalidUrl(href)) return;
      }
      e.preventDefault();
      if ((el as HTMLInputElement).type === "checkbox")
        (el as HTMLInputElement).checked = !(el as HTMLInputElement).checked;
      if (
        (el as HTMLInputElement).type === "range" &&
        (el as HTMLInputElement).dataset.startVal
      )
        (el as HTMLInputElement).value =
          (el as HTMLInputElement).dataset.startVal!;
      showToast(getMsg(el, "route_unavailable"), "error");
      el.setAttribute(DATA_FAILED_ROUTE, "true");
    });
  };

  const initRouteGuards = (): void => {
    document
      .querySelectorAll('[data-route-guard], [data-sv-localized="true"]')
      .forEach((n) => attachRouteGuard(n as HTMLElement));
    const mo = new MutationObserver((muts) => {
      for (const m of muts) {
        m.addedNodes.forEach((n) => {
          if (n.nodeType === 1) {
            const el = n as HTMLElement;
            if (
              el.matches?.(
                '[data-route-guard], [data-sv-localized="true"]',
              )
            )
              attachRouteGuard(el);
            el
              .querySelectorAll?.(
                '[data-route-guard], [data-sv-localized="true"]',
              )
              .forEach((c) => attachRouteGuard(c as HTMLElement));
          }
        });
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };

  const addAnimation = (
    el: HTMLElement | null,
    animClass: string,
    duration?: number,
  ): Promise<void> => {
    if (!el) return Promise.resolve();
    el.classList.add(animClass);
    return new Promise((r) =>
      setTimeout(() => {
        el.classList.remove(animClass);
        r();
      }, duration || 300),
    );
  };

  const fadeIn = (el: HTMLElement | null, dur?: number): Promise<void> => {
    if (!el) return Promise.resolve();
    el.style.opacity = "0";
    el.style.display = "";
    el.style.transition = `opacity ${dur || 300}ms ease`;
    requestAnimationFrame(() => (el.style.opacity = "1"));
    return new Promise((r) => setTimeout(r, dur || 300));
  };

  const fadeOut = (el: HTMLElement | null, dur?: number): Promise<void> => {
    if (!el) return Promise.resolve();
    el.style.transition = `opacity ${dur || 300}ms ease`;
    el.style.opacity = "0";
    return new Promise((r) =>
      setTimeout(() => {
        el.style.display = "none";
        r();
      }, dur || 300),
    );
  };

  const slideDown = (el: HTMLElement | null, dur?: number): Promise<void> => {
    if (!el) return Promise.resolve();
    el.style.height = "0";
    el.style.overflow = "hidden";
    el.style.display = "";
    const h = el.scrollHeight;
    el.style.transition = `height ${dur || 300}ms ease`;
    requestAnimationFrame(() => (el.style.height = h + "px"));
    return new Promise((r) =>
      setTimeout(() => {
        el.style.height = "";
        el.style.overflow = "";
        r();
      }, dur || 300),
    );
  };

  const slideUp = (el: HTMLElement | null, dur?: number): Promise<void> => {
    if (!el) return Promise.resolve();
    el.style.height = el.scrollHeight + "px";
    el.style.overflow = "hidden";
    el.style.transition = `height ${dur || 300}ms ease`;
    requestAnimationFrame(() => (el.style.height = "0"));
    return new Promise((r) =>
      setTimeout(() => {
        el.style.display = "none";
        el.style.height = "";
        el.style.overflow = "";
        r();
      }, dur || 300),
    );
  };

  const logError = (context: string, err: unknown): void => {
    devError(context, err);
  };

  const safeFetch = async (
    url: string,
    opts?: RequestInit,
  ): Promise<{
    ok: boolean;
    data?: unknown;
    status?: number;
    error?: string;
  }> => {
    if (isInvalidUrl(url)) return { ok: false, error: "Invalid URL" };
    try {
      const resp = await fetch(url, {
        ...opts,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          Accept: "application/json",
          ...(opts?.headers ?? {}),
        },
      });
      if (!resp.ok)
        return { ok: false, status: resp.status, error: resp.statusText };
      const data: unknown = await resp.json().catch(() => ({}));
      return { ok: true, data };
    } catch (e) {
      logError("safeFetch", e);
      return { ok: false, error: e instanceof Error ? e.message : String(e) };
    }
  };

  const guardById = (id: string): void => {
    if (!id) return;
    const el = document.getElementById(id);
    if (el) attachRouteGuard(el);
  };

  const guardMultiple = (...ids: (string | string[])[]): void => {
    ids.flat().forEach(guardById);
  };

  const guardFormSubmit = (
    formId: string | HTMLFormElement,
    opts?: { msgKey?: string },
  ): void => {
    const form =
      typeof formId === "string"
        ? (document.getElementById(formId) as HTMLFormElement | null)
        : formId;
    if (!form || form.getAttribute(DATA_LISTENER_ACTIVE) === "true") return;
    form.setAttribute(DATA_LISTENER_ACTIVE, "true");
    form.addEventListener("submit", (e: Event) => {
      const action = form.getAttribute("action") || "",
        url = form.getAttribute("data-url") || action || "#";
      if (!isInvalidUrl(url) && !isInvalidUrl(action)) return;
      e.preventDefault();
      showToast(
        getMsg(form, opts?.msgKey || "form_submit_route_unavailable"),
        "error",
      );
      form.setAttribute(DATA_FAILED_ROUTE, "true");
    });
  };

  const guardAllInContainer = (
    containerId: string,
    selector?: string,
  ): void => {
    const container = document.getElementById(containerId);
    if (!container) return;
    const sel =
      selector ||
      '[data-route-guard], [data-sv-localized="true"], [data-guard-msg]';
    container
      .querySelectorAll(sel)
      .forEach((n) => attachRouteGuard(n as HTMLElement));
  };

  const guardOnChange = (
    elId: string | HTMLElement,
    callback?: (e: Event, el: HTMLElement) => void,
  ): void => {
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

  const csrfToken = (): string =>
    document
      .querySelector('meta[name="csrf-token"]')
      ?.getAttribute("content") || "";

  const ajaxPost = (
    url: string,
    data: unknown,
    opts?: { errorMsg?: string; headers?: Record<string, string> },
  ): Promise<{
    ok: boolean;
    data?: unknown;
    status?: number;
    error?: string;
  }> => {
    if (isInvalidUrl(url)) {
      showToast(opts?.errorMsg || "Invalid URL", "error");
      return Promise.resolve({ ok: false, error: "Invalid URL" });
    }
    return safeFetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken(),
        ...(opts?.headers ?? {}),
      },
      body: JSON.stringify(data),
    });
  };

  window.RouteGuard = {
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

  if (document.readyState === "loading")
    document.addEventListener("DOMContentLoaded", initRouteGuards, {
      once: true,
    });
  else initRouteGuards();
})();

export {};
