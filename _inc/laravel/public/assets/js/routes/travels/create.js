(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#travel-create-link", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Create travel route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
