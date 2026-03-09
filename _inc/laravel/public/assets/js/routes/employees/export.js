(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#employee-export-btn", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Export employee route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
