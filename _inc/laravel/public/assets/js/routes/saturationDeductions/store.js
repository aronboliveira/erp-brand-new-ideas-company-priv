(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#store_saturation_deduction_form", {
    msgKey: "action_unavailable",
    fallbackMsg:
      "Store saturation deduction route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
