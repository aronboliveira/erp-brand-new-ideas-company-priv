(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  document.addEventListener("DOMContentLoaded", () => {
    guard.bindSubmitGuard("#label-edit-form", {
      msgKey: "action_unavailable",
      fallbackMsg:
        "The requested route is unavailable. Please contact technical support or your domain administrator.",
    });

    guard.bindSubmitGuard("form[data-guard-msg]", {
      msgKey: "action_unavailable",
      fallbackMsg:
        "The requested route is unavailable. Please contact technical support or your domain administrator.",
    });
  });
})();
