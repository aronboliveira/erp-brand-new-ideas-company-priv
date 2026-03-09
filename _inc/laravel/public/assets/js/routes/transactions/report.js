/**
 * @file Transaction Report Guard
 * @description Guards transaction report form using ERPGuard singleton
 * @requires ERPGuard
 */
(() => {
  const guard = window.ERPGuard;
  const utils = window.ERPUtils;

  if (!guard) {
    
    return;
  }

  const MSG_KEY = "transaction_index_unavailable";
  const FALLBACK = "Transaction index route is unavailable. Please contact technical support or your domain administrator.";

  try {
    // Guard form submission
    const form = document.getElementById("transaction_report");
    if (form && form.getAttribute("data-listener-active") !== "true") {
      form.setAttribute("data-listener-active", "true");

      // Resolve action from data attribute if needed
      const resolved = form.getAttribute("data-resolved-action") || "#";
      if (guard.isInvalidUrl(form.getAttribute("action")) && !guard.isInvalidUrl(resolved)) {
        form.setAttribute("action", resolved);
      }

      form.addEventListener("submit", e => {
        try {
          const action = form.getAttribute("action") || "#";
          if (!guard.isInvalidUrl(action)) return;

          e.preventDefault();
          const msg = utils?.getTranslation?.(MSG_KEY) ||
            form.getAttribute("data-guard-msg") ||
            FALLBACK;
          guard.showToast(msg, "error");
          form.setAttribute("data-failed-route", "true");
        } catch (_) {}
      });
    }

    // Guard reset button
    const reset = document.getElementById("transaction-report-reset");
    if (reset && reset.getAttribute("data-listener-active") !== "true") {
      reset.setAttribute("data-listener-active", "true");

      // Resolve href from data attribute if needed
      const url = reset.getAttribute("data-url") || "#";
      if (guard.isInvalidUrl(reset.getAttribute("href")) && !guard.isInvalidUrl(url)) {
        reset.setAttribute("href", url);
      }

      reset.addEventListener("click", e => {
        try {
          const href = reset.getAttribute("href") || "#";
          if (!guard.isInvalidUrl(href)) return;

          e.preventDefault();
          const msg = utils?.getTranslation?.(MSG_KEY) ||
            reset.getAttribute("data-guard-msg") ||
            FALLBACK;
          guard.showToast(msg, "error");
          reset.setAttribute("data-failed-route", "true");
        } catch (_) {}
      });
    }
  } catch (_) {}
})();
