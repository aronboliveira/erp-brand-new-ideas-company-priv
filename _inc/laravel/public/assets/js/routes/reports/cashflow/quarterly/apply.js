(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#apply-quarterly-cashflow", {
    checkFormTarget: true,
    formIdAttr: "data-form-id",
    msgKey: "action_unavailable",
    fallbackMsg:
      "Apply quarterly cashflow route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
