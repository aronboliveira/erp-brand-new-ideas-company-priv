(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#tax-create-link", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Create tax route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
