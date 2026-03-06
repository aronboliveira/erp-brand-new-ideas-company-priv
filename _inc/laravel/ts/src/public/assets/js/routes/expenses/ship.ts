/**
 * @fileoverview TypeScript version of public/assets/js/routes/expenses/ship.js
 * @generated from original JavaScript - manual review recommended
 * @module ship
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
(function (): void {
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataBound = "data-shipping-bound";
  const dataArmed = "data-shipping-error-armed";
  const qs = (s, r = document) => r.querySelector(s);
  const hasBS = () =>
    !!(
      qs('link[rel="stylesheet"][href*="bootstrap"]') ||
      qs('link[href*="bootstrap"]')
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain
    ) && !!(window.bootstrap && window.bootstrap.Toast);
  const ensureToastContainer = (): void => {
    let c = qs("#np-toast-container");
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
  const showToast = message => {
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
    const body = t.querySelector(".toast-body");
    if (body) body.textContent = message ?? errFb;
    new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
  };
  const notifyError = (host, msg) => {
    if (!host || host.getAttribute(dataArmed) === "true") return;
    host.setAttribute(dataArmed, "true");
    const handler = (): void => {
      try {
        hasBS() ? showToast(msg) : alert(msg);
      } finally {
        host.removeAttribute(dataArmed);
      }
    };
    document.addEventListener("pointerup", handler, { once: true });
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(host)) {
        document.removeEventListener("pointerup", handler);
        o.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  const localize = function (el, msgKey) {
    let msg = errFb;
    if (
      el.getAttribute(dataSvLocalized) === "true" ||
      el.getAttribute(dataClientLocalized) === "true"
    )
      msg = el.getAttribute(dataGuardMsg) || errFb;
    else {
      let lang = (
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ?? "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      const k = msgKey;
      msg =
        window.translations?.[lang]?.[k] ||
        el.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[k] ||
        errFb;
      if (msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const bindOnce = el => {
    if (!el || el.getAttribute(dataBound) === "true") return;
    el.setAttribute(dataBound, "true");
    const handler = function (): void {
      const $ = window.jQuery;
      // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
      if (!$.ajax) {
        try {
          if (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1"
          )
            console.error("jQuery or $.ajax unavailable");
        } catch (_) {}
        notifyError(document.body, localize(el, "shipping_unavailable"));
        return;
      }
      const url = el.getAttribute("data-url");
      let href = null;
      const isAnchor = el.tagName && el.tagName.toLowerCase() === "a";
      if (isAnchor) href = el.getAttribute("href");
      else if (el.form) href = el.form.getAttribute("action");
      // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
      if ((!url || url === "#") && (!href || href === "#")) {
        hasBS()
          ? showToast(localize(el, "shipping_unavailable"))
          : alert(localize(el, "shipping_unavailable"));
        return;
      }
      const is_display = $("#shipping").is(":checked");
      try {
        $.ajax({
          url: url || href,
          type: "get",
          data: { is_display: is_display },
        }).fail(function (): void {
          hasBS()
            ? showToast(localize(el, "shipping_unavailable"))
            : alert(localize(el, "shipping_unavailable"));
        });
      } catch (_) {
        hasBS()
          ? showToast(localize(el, "shipping_unavailable"))
          : alert(localize(el, "shipping_unavailable"));
      }
    };
    window.jQuery(el).on("pointerup", handler);
    const mo = new MutationObserver(function (): void {
      if (!document.body.contains(el)) {
        try {
          window.jQuery(el).off("pointerup", handler);
        } catch (_) {}
        mo.disconnect();
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  const init = (): void => {
    const el = qs("#shipping");
    if (el) bindOnce(el);
    const mo = new MutationObserver(function (): void {
      const s = qs("#shipping");
      if (s) bindOnce(s);
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();

export {};
