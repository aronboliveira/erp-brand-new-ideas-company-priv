/**
 * @file Reports Payroll Reset Route Guard
 * @description Guards payroll report reset links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("a.reset-payroll-report", {
      fallbackMsg:
        "Payroll reset route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (_) {}
})();
