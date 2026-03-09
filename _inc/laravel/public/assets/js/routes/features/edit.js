(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#feature-update-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Update Feature route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
