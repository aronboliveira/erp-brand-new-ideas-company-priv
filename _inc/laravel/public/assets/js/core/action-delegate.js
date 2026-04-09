/**
 * action-delegate.ts — CSP-safe delegated click handlers for common
 * inline-event patterns: saveAsPDF, enablecookie, confirm-submit, navigate.
 *
 * Mirror of public/assets/js/core/action-delegate.js
 * @module core/action-delegate
 */
const _emitted = {};
const devWarn = (tag, msg) => {
    if (location.hostname !== "localhost" &&
        location.hostname !== "127.0.0.1")
        return;
    const key = `${tag}:${msg}`;
    if (_emitted[key])
        return;
    _emitted[key] = true;
    console.warn(`[${tag}]`, msg);
};
(() => {
    "use strict";
    document.addEventListener("click", (e) => {
        const target = e.target;
        if (!target)
            return;
        /* [data-save-pdf] — calls window.saveAsPDF() */
        const pdfTrigger = target.closest("[data-save-pdf]");
        if (pdfTrigger) {
            e.preventDefault();
            if (typeof window.saveAsPDF === "function")
                window.saveAsPDF();
            else
                devWarn("action-delegate", "saveAsPDF not available");
            return;
        }
        /* [data-navigate-to] — redirects to the URL in the attribute */
        const navTrigger = target.closest("[data-navigate-to]");
        if (navTrigger) {
            e.preventDefault();
            const url = navTrigger.getAttribute("data-navigate-to");
            if (url)
                location.href = url;
            return;
        }
        /* [data-confirm-submit] — confirm() then submit closest form */
        const confirmTrigger = target.closest("[data-confirm-submit]");
        if (confirmTrigger) {
            e.preventDefault();
            const msg = confirmTrigger.getAttribute("data-confirm-submit") || "Are you sure?";
            if (confirm(msg)) {
                const form = confirmTrigger.closest("form");
                if (form)
                    form.submit();
            }
            return;
        }
        /* [data-call-fn] — calls a named global function */
        const fnTrigger = target.closest("[data-call-fn]");
        if (fnTrigger) {
            e.preventDefault();
            const fnName = fnTrigger.getAttribute("data-call-fn");
            if (fnName && typeof window[fnName] === "function")
                window[fnName]();
        }
    });
})();
//# sourceMappingURL=action-delegate.js.map