(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#taskboard-view-grid", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "View taskboard route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
