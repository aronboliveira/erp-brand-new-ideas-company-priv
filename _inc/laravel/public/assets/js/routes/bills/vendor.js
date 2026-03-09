/**
 * @file Bill Vendor Route Guard
 * @description Guards bill vendor select changes using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindChangeGuard("#vendor", {
      fallbackMsg:
        "Bill vendor route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (_) {}
})();
