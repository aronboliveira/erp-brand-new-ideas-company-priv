/**
 * @fileoverview TypeScript version of public/assets/js/routes/reports/trials/print.js
 * @generated from original JavaScript - manual review recommended
 * @module print
 */
/* eslint-disable @typescript-eslint/explicit-function-return-type, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars */

/* global bootstrap */
(function (): void {
  const qs = (s, r = document) => r.querySelector(s);
  const errFb = "# ERROR";
  const dataClientLocalized = "data-client-localized";
  const dataGuardMsg = "data-guard-msg";
  const dataErrGuard = "data-error-guard";
  const dataPrintBound = "data-print-init-bound";
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
    const hasBsLink =
      qs('link[rel="stylesheet"][href*="bootstrap"]') ||
      qs('link[href*="bootstrap"]');
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/prefer-optional-chain, @typescript-eslint/strict-boolean-expressions
    const hasBsToast = window.bootstrap && window.bootstrap.Toast;
    // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
    if (hasBsLink && hasBsToast) {
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
  const scheduleInteractiveError = message => {
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
  };
  const getMsg = (el, msgKey) => {
    const errFbL = errFb;
    const dataClientLocalizedL = dataClientLocalized;
    const dataGuardMsgL = dataGuardMsg;
    let msg = errFbL;
    if (
      el.getAttribute("data-sv-localized") === "true" ||
      el.getAttribute(dataClientLocalizedL) === "true"
    ) {
      msg = el.getAttribute(dataGuardMsgL) || errFbL;
    } else {
      let lang = (
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition, @typescript-eslint/strict-boolean-expressions
        window.sessionStorage.getItem("erp-np-lang") ??
        document.documentElement.lang ?? "en"
      )
        .toLowerCase()
        .replace(/_/g, "-");
      lang = lang === "pt-br" ? lang : lang.slice(0, 2);
      msg =
        window.translations?.[lang]?.[msgKey] ||
        el.getAttribute(dataGuardMsgL) ||
        window.translations?.en?.[msgKey] ||
        errFbL;
      if (msg !== errFbL) {
        el.setAttribute(dataGuardMsgL, msg);
        el.setAttribute(dataClientLocalized, "true");
      }
    }
    return msg;
  };
  const goBack = (): void => {
    try {
      window.close();
    } catch (_) {}
    try {
      // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions, @typescript-eslint/no-unnecessary-condition
      if (window.history && typeof window.history.back === "function") {
        window.history.back();
      }
    } catch (_) {}
  };
  const init = (): void => {
    const root = document.documentElement;
    if (root.getAttribute(dataPrintBound) === "true") {
      return;
    }
    root.setAttribute(dataPrintBound, "true");
    let printed = false;
    const onAfterPrint = (): void => {
      printed = true;
      goBack();
    };
    if ("onafterprint" in window) {
      window.onafterprint = onAfterPrint;
    } else {
      window.addEventListener("afterprint", onAfterPrint);
    }
    try {
      if (typeof window.print === "function") {
        window.print();
      } else {
        scheduleInteractiveError(getMsg(document.body, "print_unavailable"));
      }
    } catch (_) {
      scheduleInteractiveError(getMsg(document.body, "print_unavailable"));
    }
    setTimeout(function (): void {
      if (!printed) {
        scheduleInteractiveError(getMsg(document.body, "print_unavailable"));
      }
    }, 2000);
    const mo = new MutationObserver((m, o) => {
      if (!document.documentElement.isConnected) {
        window.removeEventListener("afterprint", onAfterPrint);
        o.disconnect();
      }
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
