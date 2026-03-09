(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#pills-home-tab", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Monthly cashflow route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
