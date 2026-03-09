(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#designation-store-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Create designation route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
