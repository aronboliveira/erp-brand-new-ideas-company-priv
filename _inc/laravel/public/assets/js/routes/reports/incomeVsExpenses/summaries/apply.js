/**
 * Income vs Expense Summary Report Apply Route Guards
 * Handles income vs expense summary apply button with form submission
 * @module routes/reports/incomeVsExpenses/summaries/apply
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("a.apply-income-vs-expense-summary", {
    checkFormTarget: true,
    formIdAttr: "data-form-id",
    fallbackMsg:
      "Income vs expense summary report route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
