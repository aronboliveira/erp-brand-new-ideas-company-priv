(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#pipeline-update-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Update Pipeline route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
