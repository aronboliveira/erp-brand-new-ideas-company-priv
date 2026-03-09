(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#receivables-print", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Print receivables route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
