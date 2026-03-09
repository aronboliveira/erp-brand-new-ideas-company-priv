/**
 * Payroll Report Apply Route Guards
 * Handles payroll report apply button with form submission
 * @module routes/reports/payrolls/apply
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("a.apply-payroll-report", {
    checkFormTarget: true,
    formIdAttr: "data-form-id",
    fallbackMsg:
      "Payroll apply route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
