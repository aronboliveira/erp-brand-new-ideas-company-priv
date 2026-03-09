(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#reset-receivables-index", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Reset receivables route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
