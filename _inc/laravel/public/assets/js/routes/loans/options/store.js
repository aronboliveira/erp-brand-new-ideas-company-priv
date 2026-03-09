(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#ln-opt-store-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Loan option store route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
