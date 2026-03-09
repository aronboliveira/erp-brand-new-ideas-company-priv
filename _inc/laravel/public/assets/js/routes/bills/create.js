(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#bill-create-btn", {
    msgKey: "action_unavailable",
    fallbackMsg: "# ERROR",
  });
})();
