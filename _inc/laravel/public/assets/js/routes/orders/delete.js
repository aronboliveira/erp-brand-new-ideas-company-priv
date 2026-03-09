(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("a[data-guard-msg], a[data-url]", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Requested route is unavailable. Please contact technical support or your domain administrator.",
  });
  guard.bindSubmitGuard("form[data-guard-msg], form[data-url]", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Requested route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
