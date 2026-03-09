(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#profit-loss-print", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Print profit and loss route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
