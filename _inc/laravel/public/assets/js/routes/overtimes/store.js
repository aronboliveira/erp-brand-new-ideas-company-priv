(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#ovt-store-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Overtime store route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
