(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#holiday-gen-ai", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "AI generation route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
