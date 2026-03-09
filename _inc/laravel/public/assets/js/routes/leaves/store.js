(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#store_leave", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Create leave route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
