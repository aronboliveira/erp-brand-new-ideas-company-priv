(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#reset-quarterly-cashflow", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Reset quarterly cashflow route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
