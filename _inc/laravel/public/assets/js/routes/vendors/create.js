(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#vendor-create-link", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Create vendor route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
