/**
 * @file Reports Expense Summary Reset Route Guard
 * @description Guards expense summary report reset links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("a.reset-expense-summary", {
      fallbackMsg:
        "Expense summary report route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (_) {}
})();
