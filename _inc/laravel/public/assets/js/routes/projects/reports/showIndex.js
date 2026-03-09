(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#project-report-index-link", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Project report index route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
