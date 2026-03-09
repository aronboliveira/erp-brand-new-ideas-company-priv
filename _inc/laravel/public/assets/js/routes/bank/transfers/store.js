(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#bank-transfer-form", {
    msgKey: "action_unavailable",
    fallbackMsg: "# ERROR",
  });
})();
