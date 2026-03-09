(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#form-checklist", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Store task checklist route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
