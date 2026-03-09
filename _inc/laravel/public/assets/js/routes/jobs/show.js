(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#job-edit-link", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Edit Job route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
