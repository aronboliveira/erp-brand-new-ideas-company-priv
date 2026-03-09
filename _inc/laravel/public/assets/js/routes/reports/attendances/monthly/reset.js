/**
 * @file Reports Monthly Attendance Reset Route Guard
 * @description Guards monthly attendance report reset links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("a.reset-monthly-attendance", {
      fallbackMsg:
        "Monthly attendance reset route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (_) {}
})();
