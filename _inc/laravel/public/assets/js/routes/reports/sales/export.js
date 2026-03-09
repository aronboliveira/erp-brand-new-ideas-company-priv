(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#sales-report-export", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Export sales report route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
