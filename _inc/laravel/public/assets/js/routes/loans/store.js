(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#ln-store-form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Loan store route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
