(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#training-status-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Update training status route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
