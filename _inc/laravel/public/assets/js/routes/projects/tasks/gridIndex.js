(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#project-index-link", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Project index route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
