(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard(".tax-edit-link, .dashboard-link", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Target route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
