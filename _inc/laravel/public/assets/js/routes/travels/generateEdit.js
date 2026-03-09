(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#travel-generate-link", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Generate content route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
