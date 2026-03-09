(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#reset-profit-loss-summary", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Reset profit & loss summary route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
