/**
 * @file Termination Type Update Route Guard
 * @description Guards termination type update form using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindSubmitGuard("#termination-type-update-form", {
      fallbackMsg:
        "Update termination type route is unavailable. Please contact technical support or your domain administrator.",
      handler(event, form) {
        event.preventDefault();
        const action = form.getAttribute("action") || "#";
        const url = form.getAttribute("data-url") || action || "#";
        if (action !== "#" && url !== "#") {
          form.submit();
          return;
        }
        const msg =
          form.getAttribute("data-guard-msg") ||
          "Update termination type route is unavailable. Please contact technical support or your domain administrator.";
        guard.showToast(msg);
        form.setAttribute("data-failed-route", "true");
      },
    });
  } catch (_) {}
})();
