/**
 * @fileoverview TypeScript version of public/assets/js/routes/vendors/copy.js
 * @generated from original JavaScript - manual review recommended
 * @module copy
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
(function (): void {
  const $ = window.jQuery;
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataBindGuard = "data-copy-billing-bound";
  const qs = (s, r = document) => r.querySelector(s);
  const hasBootstrapUi = () =>
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
  const showErrorNow = message => {
    if (hasBootstrapUi()) {
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
  const getMsg = (el, key) => {
    let msg = errFb;
    if (
      el?.getAttribute?.(dataSvLocalized) === "true" ||
      el?.getAttribute?.(dataClientLocalized) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsg) || errFb;
    } else {
      let lang = (
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ?? "en"
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
  const copyValue = (from, to) => {
    const $from = $(`[name='${from}']`);
    const $to = $(`[name='${to}']`);
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
    if (!$from.length || !$to.length) return false;
    const v = ($from.val() ?? "").toString();
    $to.val(v);
    return true;
  };
  const handler = function (): void {
    try {
      // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
      if (!$.fn) {
        try {
          console.error("jQuery unavailable");
        } catch (_) {}
        showErrorNow(getMsg(this, "plugin_unavailable"));
        return;
      }
      const ok = [
        ["billing_name", "shipping_name"],
        ["billing_country", "shipping_country"],
        ["billing_state", "shipping_state"],
        ["billing_city", "shipping_city"],
        ["billing_phone", "shipping_phone"],
        ["billing_zip", "shipping_zip"],
        ["billing_address", "shipping_address"],
      ]
        .map(function (p) {
          return copyValue(p[0], p[1]);
        })
        .every(function (x) {
          return x;
        });
      if (!ok) showErrorNow(getMsg(this, "copy_billing_unavailable"));
    } catch (_) {
      showErrorNow(getMsg(this, "copy_billing_unavailable"));
    }
  };
  const bind = (): void => {
    const host = document.body;
    if (host.getAttribute(dataBindGuard) === "true") return;
    host.setAttribute(dataBindGuard, "true");
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain
    if ($ && $.fn) {
      $(document).on("click._npCopy", "#billing_data", handler);
    } else {
      document.addEventListener("click", function (e) {
        const t = e.target;
        if (t && (t.id === "billing_data" || t.closest?.("#billing_data")))
          handler.call(t);
      });
    }
    const mo = new MutationObserver(function (): void {
      if (!document.querySelector<HTMLElement>("#billing_data")) {
        // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain
        if ($ && $.fn) {
          $(document).off("click._npCopy", "#billing_data");
        }
        host.removeAttribute(dataBindGuard);
      }
    });
    mo.observe(document.documentElement, { childList: true, subtree: true });
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bind, { once: true });
  } else {
    bind();
  }
})();

export {};
