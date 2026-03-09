/**
 * Payables Report Apply Route Guards
 * Handles payables report apply button with form submission
 * @module routes/reports/payables/index/apply
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("a.apply-payables", {
    checkFormTarget: true,
    formIdAttr: "data-form-id",
    fallbackMsg:
      "Payables apply route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
