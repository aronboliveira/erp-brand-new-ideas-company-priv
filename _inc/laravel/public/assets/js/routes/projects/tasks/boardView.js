(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#taskboard-view-list-btn", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Taskboard list view route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
