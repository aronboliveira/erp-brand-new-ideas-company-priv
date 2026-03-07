/**
 * @fileoverview TypeScript version of public/assets/js/routes/installer/dismiss.js
 * @generated from original JavaScript - manual review recommended
 * @module dismiss
 */

/* global bootstrap */
(function (): void {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataBindGuard = "data-dismiss-bound";
  const dataErrGuard = "data-dismiss-error";
  const qs = <T extends Element = HTMLElement>(
    s: string,
    r: Document | Element = document,
  ): T | null => r.querySelector(s) as T | null;
  const hasBS = () =>
    !!(
      qs('link[rel="stylesheet"][href*="bootstrap"]') ||
      qs('link[href*="bootstrap"]')
    ) && !!window.bootstrap?.Toast;
  const toastContainer = (): HTMLDivElement => {
    let c = qs<HTMLDivElement>("#np-toast-container");
    if (c) return c;
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
  const showError = (message: string) => {
    if (hasBS()) {
      const container = toastContainer();
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
      const body = t.querySelector(".toast-body");
      if (body) body.textContent = message ?? errFb;
      try {
        new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
      } catch (_) {
        alert(message ?? errFb);
      }
    } else {
      alert(message ?? errFb);
    }
  };
  const scheduleClickError = (msg: string) => {
    const host = document.body;
    if (!host || host.getAttribute(dataErrGuard) === "true") return;
    host.setAttribute(dataErrGuard, "true");
    const once = (): void => {
      try {
        showError(msg);
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
  const localize = (el: HTMLElement, key: string) => {
    let msg = errFb;
    if (
      el?.getAttribute?.(dataSvLocalized) === "true" ||
      el?.getAttribute?.(dataClientLocalized) === "true"
    )
      msg = el.getAttribute(dataGuardMsg) || errFb;
    else {
      let lang = (
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ??
        "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const msgKey = key;
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
  const hideAlert = (closeEl: HTMLElement) => {
    try {
      const target = qs("#error_alert");
      if (!target) {
        scheduleClickError(localize(closeEl, "dismiss_unavailable"));
        return;
      }
      target.style.display = "none";
    } catch (_) {
      scheduleClickError(localize(closeEl, "dismiss_unavailable"));
    }
  };
  const bind = (): void => {
    const closeEl = qs("#close_alert");
    if (!closeEl) return;
    if (closeEl.getAttribute(dataBindGuard) === "true") return;
    closeEl.setAttribute(dataBindGuard, "true");
    const onClick = function (this: HTMLElement, e: Event) {
      e.preventDefault();
      hideAlert(this);
    };
    closeEl.addEventListener("click", onClick, { passive: false });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(closeEl)) {
        closeEl.removeEventListener("click", onClick);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  if (document.readyState === "loading")
    document.addEventListener("DOMContentLoaded", bind, { once: true });
  else bind();
})();

export {};
