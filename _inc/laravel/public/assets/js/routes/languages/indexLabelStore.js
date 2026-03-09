(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#langStoreForm", {
    msgKey: "action_unavailable",
    fallbackMsg: "# ERROR",
  });
})();
