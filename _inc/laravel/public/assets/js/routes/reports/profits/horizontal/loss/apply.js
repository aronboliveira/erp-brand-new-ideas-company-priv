(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#apply-profit-loss-horizontal", {
    checkFormTarget: true,
    formIdAttr: "data-form-id",
    msgKey: "action_unavailable",
    fallbackMsg:
      "Apply profit & loss route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
