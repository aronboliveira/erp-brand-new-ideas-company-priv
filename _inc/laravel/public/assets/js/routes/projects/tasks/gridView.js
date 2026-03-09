(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#taskboard-grid-view-btn", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Taskboard grid view route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
