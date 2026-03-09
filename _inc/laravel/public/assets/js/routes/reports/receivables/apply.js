(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#apply-receivables-index", {
    checkFormTarget: true,
    formIdAttr: "data-form-id",
    msgKey: "action_unavailable",
    fallbackMsg:
      "Apply receivables route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
