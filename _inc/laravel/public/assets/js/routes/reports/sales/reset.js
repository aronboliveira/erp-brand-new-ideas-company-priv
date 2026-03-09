(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#reset-sales-index", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Reset sales route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
