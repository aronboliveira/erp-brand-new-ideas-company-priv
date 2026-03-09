(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#jobOnBoard-update-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Update Job On Board route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
