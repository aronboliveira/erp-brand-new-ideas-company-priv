(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#training-generate-link-edit", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Generate training content route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
