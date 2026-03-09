(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#edit_transfer", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Update transfer route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
