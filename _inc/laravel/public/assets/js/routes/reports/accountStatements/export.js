(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#account-statements-export", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Export account statements route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
