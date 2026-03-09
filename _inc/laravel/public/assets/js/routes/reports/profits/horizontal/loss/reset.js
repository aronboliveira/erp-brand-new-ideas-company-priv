(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#reset-profit-loss-horizontal", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Reset profit & loss route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
