(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#languages-store-data-form", {
    msgKey: "action_unavailable",
    fallbackMsg: "# ERROR",
  });
})();
