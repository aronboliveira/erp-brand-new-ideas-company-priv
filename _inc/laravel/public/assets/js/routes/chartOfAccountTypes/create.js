(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#createTypeBtn", {
    msgKey: "action_unavailable",
    fallbackMsg: "# ERROR",
  });
})();
