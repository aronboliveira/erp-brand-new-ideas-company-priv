/**
 * @file Job Application Show Route Guard
 * @description Guards job application routes and initializes tooltips using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  const initTooltips = () => {
    try {
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        try {
          window.bootstrap?.Tooltip.getOrCreateInstance(el);
        } catch {}
      });
    } catch {}
  };

  document.addEventListener("DOMContentLoaded", () => {
    guard.bindClickGuard("a[data-guard-msg],a[data-url]", {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Requested route is unavailable. Please contact technical support or your domain administrator.",
    });

    guard.bindSubmitGuard("form[data-guard-msg],form[data-url]", {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Requested route is unavailable. Please contact technical support or your domain administrator.",
    });

    initTooltips();
  });
})();
