/**
 * @fileoverview TypeScript version of public/assets/js/routes/timeTrackers/time.js
 * @generated from original JavaScript - manual review recommended
 * @module time
 */

declare global {
  interface JQuery {
    timeEntry?(options?: { show24Hours?: boolean }): JQuery;
  }
}

/* global bootstrap, $, jQuery */
(function (): void {
  const $ = window.jQuery;
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-timeentry-error";
  const dataInitGuard = "data-timeentry-initialized";
  const qs = (s: string, r: Document | Element = document) =>
    r.querySelector(s);
  const hasBootstrap = () =>
    qs('link[rel="stylesheet"][href*="bootstrap"]') ||
    (qs('link[href*="bootstrap"]') && window.bootstrap?.Toast);
  const ensureToastContainer = (): HTMLDivElement => {
    let c = qs("#np-toast-container") as HTMLDivElement | null;
    if (c) {
      return c;
    }
    c = document.createElement("div");
    c.id = "np-toast-container";
    c.setAttribute("aria-live", "polite");
    c.setAttribute("aria-atomic", "true");
    c.style.position = "fixed";
    c.style.top = "1rem";
    c.style.right = "1rem";
    document.body.appendChild(c);
    return c;
  };
  const showErrorNow = (message: string) => {
    if (hasBootstrap()) {
      const container = ensureToastContainer();
      let t = qs("#np-toast", container);
      if (!t) {
        t = document.createElement("div");
        t.id = "np-toast";
        t.className = "toast";
        t.setAttribute("role", "alert");
        t.setAttribute("aria-live", "assertive");
        t.setAttribute("aria-atomic", "true");
        t.innerHTML =
          '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
        container.appendChild(t);
      }
      const body = qs(".toast-body", t);
      if (body) {
        body.textContent = message ?? errFb;
      }
      try {
        new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
      } catch (_) {
        alert(message ?? errFb);
      }
    } else {
      alert(message ?? errFb);
    }
  };
  const scheduleClickError = (message: string) => {
    const host = document.body;
    if (!host || host.getAttribute(dataErrGuard) === "true") {
      return;
    }
    host.setAttribute(dataErrGuard, "true");
    const once = (): void => {
      try {
        showErrorNow(message);
      } finally {
        host.removeAttribute(dataErrGuard);
      }
    };
    document.addEventListener("click", once, { once: true });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(host)) {
        document.removeEventListener("click", once);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  const getMsg = (el: HTMLElement, msgKey: string) => {
    let msg = errFb;
    if (
      el?.getAttribute?.(dataSvLocalized) === "true" ||
      el?.getAttribute?.(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ??
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el?.getAttribute?.(dataGuardMsg) ||
        window.translations?.en?.[msgKey] ||
        errFb;
      if (msg !== errFb && el) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const initTimeInputs = (): void => {
    if (!$ || !$.fn) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("jQuery unavailable");
      } catch (_) {}
      scheduleClickError(getMsg(document.body, "plugin_unavailable"));
      return;
    }
    if (!$.fn.timeEntry) {
      try {
        if (
          window.location.hostname === "localhost" ||
          window.location.hostname === "127.0.0.1"
        )
          console.error("timeEntry unavailable");
      } catch (_) {}
      scheduleClickError(getMsg(document.body, "time_unavailable"));
      return;
    }
    const $targets = $('[data-type="times"]');
    if (!$targets.length) {
      return;
    }
    $targets.each(function (): void {
      const el = this;
      if ($(el).data("timeEntry")) {
        return;
      }
      try {
        $(el).timeEntry!({ show24Hours: true });
      } catch (_) {
        scheduleClickError(getMsg(el, "time_unavailable"));
      }
    });
  };
  const guard = document.body;
  if (guard.getAttribute(dataInitGuard) !== "true") {
    guard.setAttribute(dataInitGuard, "true");
    initTimeInputs();
    const mo = new MutationObserver((): void => {
      initTimeInputs();
    });
    mo.observe(document.body, { childList: true, subtree: true });
  }
})();

export {};
