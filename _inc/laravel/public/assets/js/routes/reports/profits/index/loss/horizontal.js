(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#profit-loss-horizontal-open", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Horizontal profit & loss view route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
