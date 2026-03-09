(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#taskboard-list-view-link", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "List taskboard route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
