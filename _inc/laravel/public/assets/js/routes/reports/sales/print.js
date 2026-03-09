(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#sales-report-print", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Print sales report route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
