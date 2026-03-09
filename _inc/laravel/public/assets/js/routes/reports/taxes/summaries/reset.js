(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#reset-tax-summary", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Reset tax summary route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
