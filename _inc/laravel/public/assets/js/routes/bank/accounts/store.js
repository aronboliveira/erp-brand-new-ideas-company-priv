(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#bank-account-store-form", {
    msgKey: "action_unavailable",
    fallbackMsg: "# ERROR",
  });
})();
