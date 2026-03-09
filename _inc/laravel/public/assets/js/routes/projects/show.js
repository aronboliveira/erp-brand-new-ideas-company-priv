(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("a.project-show-link", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Show project route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
