/**
 * Monthly Cashflow Report Apply Route Guards
 * Handles monthly cashflow report apply button with form submission
 * @module routes/reports/cashflow/monthly/apply
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("a.apply-monthly-cashflow", {
    checkFormTarget: true,
    formIdAttr: "data-form-id",
    fallbackMsg:
      "Monthly cashflow apply route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
