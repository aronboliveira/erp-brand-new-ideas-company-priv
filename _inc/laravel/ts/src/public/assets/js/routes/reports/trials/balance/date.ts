/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/trials/balance/date.js
 * @generated from original JavaScript - manual review recommended
 * @module date
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars */

/* global bootstrap, $, jQuery */
(function (): void {
  const $ = window.jQuery;
  const qs = (s, r = document) => r.querySelector(s);
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataSvLocalized = "data-sv-localized";
  const dataErrGuard = "data-error-guard";
  const dataInitGuard = "data-date-sync-init";
  const dataListenerGuard = "data-date-sync-listener";
  // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
  if (!$) {
    try {
      if (
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1"
      )
        console.error("jQuery unavailable");
    } catch (_) {}
    scheduleInteractiveError(getMsg(document.body, "plugin_unavailable"));
    return;
  }
  const ensureToastContainer = (): void => {
    const id = "np-toast-container";
    let c = qs("#" + id);
    if (c) {
      return c;
    }
    c = document.createElement("div");
    c.id = id;
    c.setAttribute("aria-live", "polite");
    c.setAttribute("aria-atomic", "true");
    c.style.position = "fixed";
    c.style.top = "1rem";
    c.style.right = "1rem";
    document.body.appendChild(c);
    return c;
  };
  const showErrorNow = message => {
    const hasBootstrap =
      (qs('link[rel="stylesheet"][href*="bootstrap"]') ||
        qs('link[href*="bootstrap"]')) &&
      // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain, @typescript-eslint/strict-boolean-expressions
      window.bootstrap &&
      window.bootstrap.Toast;
    if (hasBootstrap) {
      const container = ensureToastContainer();
      const tid = "np-toast";
      let t = qs("#" + tid, container);
      if (!t) {
        t = document.createElement("div");
        t.id = tid;
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
  function scheduleInteractiveError(message) {
    const host = document.body;
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
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
  }
  const getMsg = (el, key) => {
    let msg = errFb;
    if (
      el?.getAttribute(dataSvLocalized) === "true" ||
      el?.getAttribute(dataClientLocalized) === "true"
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
        el?.getAttribute(dataGuardMsg) ||
        window.translations?.en?.[msgKey] ||
        errFb;
      if (el && msg !== errFb) {
        el.setAttribute(dataGuardMsg, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const bindWithObserver = (el, evt, handler, flag) => {
    if (!el || el.getAttribute(flag) === "true") {
      return;
    }
    el.setAttribute(flag, "true");
    $(el).on(evt, handler);
    const mo = new MutationObserver((m, o) => {
      if (!document.body.contains(el)) {
        $(el).off(evt, handler);
        o.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  };
  const syncDates = (): void => {
    try {
      const startVal = $(".startDate").val() ?? "";
      const endVal = $(".endDate").val() ?? "";
      const hasTargets = $(".start_date").length + $(".end_date").length > 0;
      if (!hasTargets) {
        scheduleInteractiveError(getMsg(document.body, "date_sync_failed"));
        return;
      }
      $(".start_date").val(startVal);
      $(".end_date").val(endVal);
    } catch (_) {
      scheduleInteractiveError(getMsg(document.body, "date_sync_failed"));
    }
  };
  const init = (): void => {
    const root = document.documentElement;
    if (root.getAttribute(dataInitGuard) === "true") {
      return;
    }
    root.setAttribute(dataInitGuard, "true");
    syncDates();
    document.querySelectorAll(".startDate, .endDate").forEach(function (el) {
      bindWithObserver(el, "change", syncDates, dataListenerGuard);
    });
  };
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();

export {};
