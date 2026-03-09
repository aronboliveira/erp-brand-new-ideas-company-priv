(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#tax-index-link", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Tax index route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
