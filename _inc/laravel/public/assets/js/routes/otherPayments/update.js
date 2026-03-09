(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#otherpayment-update-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Update route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
