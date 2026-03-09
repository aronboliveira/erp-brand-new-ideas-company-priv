/**
 * Expense Summary Report Apply Route Guards
 * Handles expense summary report apply button with form submission
 * @module routes/reports/expenses/summaries/apply
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("a.apply-expense-summary", {
    checkFormTarget: true,
    formIdAttr: "data-form-id",
    fallbackMsg:
      "Expense summary report route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
