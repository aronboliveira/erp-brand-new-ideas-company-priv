(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#profit-loss-vertical-view", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Vertical profit & loss view route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
