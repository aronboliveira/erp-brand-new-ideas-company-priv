(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#create_employee", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Requested route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
