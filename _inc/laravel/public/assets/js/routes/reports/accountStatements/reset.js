(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#reset-account-statement", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Reset account statement route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
