/**
 * action-delegate.ts — CSP-safe delegated click handlers for common
 * inline-event patterns: saveAsPDF, enablecookie, confirm-submit, navigate.
 *
 * Mirror of public/assets/js/core/action-delegate.js
 * @module core/action-delegate
 */

const _emitted: Record<string, true> = {};

const devWarn = (tag: string, msg: string): void => {
  if (
    location.hostname !== "localhost" &&
    location.hostname !== "127.0.0.1"
  )
    return;
  const key = `${tag}:${msg}`;
  if (_emitted[key]) return;
  _emitted[key] = true;
  console.warn(`[${tag}]`, msg);
};

((): void => {
  "use strict";

  document.addEventListener("click", (e: MouseEvent) => {
    const target = e.target as HTMLElement | null;
    if (!target) return;

    /* [data-save-pdf] — calls window.saveAsPDF() */
    const pdfTrigger = target.closest<HTMLElement>("[data-save-pdf]");
    if (pdfTrigger) {
      e.preventDefault();
      if (typeof window.saveAsPDF === "function") window.saveAsPDF();
      else devWarn("action-delegate", "saveAsPDF not available");
      return;
    }

    /* [data-navigate-to] — redirects to the URL in the attribute */
    const navTrigger = target.closest<HTMLElement>("[data-navigate-to]");
    if (navTrigger) {
      e.preventDefault();
      const url = navTrigger.getAttribute("data-navigate-to");
      if (url) location.href = url;
      return;
    }

    /* [data-confirm-submit] — confirm() then submit closest form */
    const confirmTrigger = target.closest<HTMLElement>("[data-confirm-submit]");
    if (confirmTrigger) {
      e.preventDefault();
      const msg =
        confirmTrigger.getAttribute("data-confirm-submit") || "Are you sure?";
      if (confirm(msg)) {
        const form = confirmTrigger.closest("form");
        if (form) form.submit();
      }
      return;
    }

    /* [data-call-fn] — calls a named global function */
    const fnTrigger = target.closest<HTMLElement>("[data-call-fn]");
    if (fnTrigger) {
      e.preventDefault();
      const fnName = fnTrigger.getAttribute("data-call-fn");
      if (fnName && typeof (window as unknown as Record<string, unknown>)[fnName] === "function")
        (window as unknown as Record<string, CallableFunction>)[fnName]();
    }
  });
})();

export {};
