(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#apply-profit-loss-summary", {
    checkFormTarget: true,
    formIdAttr: "data-form-id",
    msgKey: "action_unavailable",
    fallbackMsg:
      "Apply profit & loss summary route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
