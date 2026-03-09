/**
 * @file Reports Income vs Expense Summary Reset Route Guard
 * @description Guards income vs expense summary report reset links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("a.reset-income-vs-expense-summary", {
      fallbackMsg:
        "Income vs expense summary report route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (_) {}
})();
