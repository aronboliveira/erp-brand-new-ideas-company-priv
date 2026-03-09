(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#ai-generate-transfer", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Generate AI bank transfer route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
