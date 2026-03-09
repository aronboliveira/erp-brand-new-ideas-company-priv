/**
 * @file Reports Monthly Cashflow Reset Route Guard
 * @description Guards monthly cashflow report reset links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("a.reset-monthly-cashflow", {
      fallbackMsg:
        "Monthly cashflow reset route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (_) {}
})();
