/**
 * @file action-delegate.js
 * @description CSP-safe delegated click handlers for common inline-event
 *   patterns: saveAsPDF, enablecookie, confirm-submit, navigate.
 */
(() => {
  "use strict";
  document.addEventListener("click", e => {
    /* [data-save-pdf] — calls window.saveAsPDF() */
    const pdfTrigger = e.target.closest("[data-save-pdf]");
    if (pdfTrigger) {
      e.preventDefault();
      if (typeof window.saveAsPDF === "function") window.saveAsPDF();
      return;
    }

    /* [data-navigate-to] — redirects to the URL in the attribute */
    const navTrigger = e.target.closest("[data-navigate-to]");
    if (navTrigger) {
      e.preventDefault();
      const url = navTrigger.getAttribute("data-navigate-to");
      if (url) location.href = url;
      return;
    }

    /* [data-confirm-submit] — confirm() then submit closest form */
    const confirmTrigger = e.target.closest("[data-confirm-submit]");
    if (confirmTrigger) {
      e.preventDefault();
      const msg = confirmTrigger.getAttribute("data-confirm-submit") || "Are you sure?";
      if (confirm(msg)) {
        const form = confirmTrigger.closest("form");
        if (form) form.submit();
      }
      return;
    }

    /* [data-call-fn] — calls a named global function */
    const fnTrigger = e.target.closest("[data-call-fn]");
    if (fnTrigger) {
      e.preventDefault();
      const fnName = fnTrigger.getAttribute("data-call-fn");
      if (fnName && typeof window[fnName] === "function") window[fnName]();
    }
  });
})();
