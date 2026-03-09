/**
 * @file Reports Payables Reset Route Guard
 * @description Guards payables report reset links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("a.reset-payables", {
      fallbackMsg:
        "Payables reset route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (_) {}
})();
