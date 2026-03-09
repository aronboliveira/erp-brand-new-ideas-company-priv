(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#budget-cancel-btn-quarterly", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Cancel quarterly budget route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
