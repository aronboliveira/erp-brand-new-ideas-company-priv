(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#ip-create-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Create IP route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
