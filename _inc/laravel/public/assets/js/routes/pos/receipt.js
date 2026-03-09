(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#pos-receipt-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Create pos receipt route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
