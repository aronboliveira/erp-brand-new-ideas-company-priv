(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard('[data-listener-alias="cancel-invoice"]', {
    msgKey: "action_unavailable",
    fallbackMsg: "# ERROR",
  });
})();
