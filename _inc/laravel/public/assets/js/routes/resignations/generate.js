(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#resignation-generate-link", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Generate resignation route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
