(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  const initTooltips = () => {
    try {
      const elements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
      elements.forEach(el => {
        try {
          if (window.bootstrap?.Tooltip) {
            bootstrap.Tooltip.getOrCreateInstance(el);
          }
        } catch (_) {}
      });
    } catch (_) {}
  };

  document.addEventListener("DOMContentLoaded", () => {
    guard.bindClickGuard("a[data-guard-msg], a[data-url]", {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Requested route is unavailable. Please contact technical support or your domain administrator.",
    });

    guard.bindSubmitGuard("form[data-guard-msg], form[data-url]", {
      msgKey: "action_unavailable",
      fallbackMsg:
        "Requested route is unavailable. Please contact technical support or your domain administrator.",
    });

    initTooltips();
  });
})();
