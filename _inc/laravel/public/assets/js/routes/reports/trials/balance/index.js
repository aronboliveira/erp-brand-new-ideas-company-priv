/**
 * @file Trial Balance Report Guard
 * @description Guards trial balance report form using ERPGuard singleton
 * @requires ERPGuard
 */
(() => {
  const guard = window.ERPGuard;
  const utils = window.ERPUtils;

  if (!guard) {
    
    return;
  }

  const FORM_ID = "report_trial_balance";
  const APPLY_ID = "trial-balance-apply";
  const MSG_KEY = "trial_balance_unavailable";
  const FALLBACK = "Trial balance report route is unavailable. Please contact technical support or your domain administrator.";

  try {
    // Guard form submission
    const form = document.getElementById(FORM_ID);
    if (form && form.getAttribute("data-submit-listener") !== "true") {
      form.setAttribute("data-submit-listener", "true");

      form.addEventListener("submit", e => {
        try {
          const action = form.getAttribute("action") || "#";
          const url = form.getAttribute("data-url") || action;

          if (!guard.isInvalidUrl(action) && !guard.isInvalidUrl(url)) return;

          e.preventDefault();
          const msg = utils?.getTranslation?.(MSG_KEY) ||
            form.getAttribute("data-guard-msg") ||
            FALLBACK;
          guard.showToast(msg, "error");
          form.setAttribute("data-failed-route", "true");
        } catch (_) {}
      }, { passive: false });
    }

    // Guard apply button
    const apply = document.getElementById(APPLY_ID);
    if (apply && apply.getAttribute("data-click-listener") !== "true") {
      apply.setAttribute("data-click-listener", "true");

      apply.addEventListener("click", e => {
        try {
          const href = apply.getAttribute("href") || "#";
          const url = apply.getAttribute("data-url") || href;

          if (!guard.isInvalidUrl(href) && !guard.isInvalidUrl(url)) return;

          e.preventDefault();
          const msg = utils?.getTranslation?.(MSG_KEY) ||
            apply.getAttribute("data-guard-msg") ||
            FALLBACK;
          guard.showToast(msg, "error");
          apply.setAttribute("data-failed-route", "true");
        } catch (_) {}
      }, { passive: false });
    }
  } catch (_) {}
})();
