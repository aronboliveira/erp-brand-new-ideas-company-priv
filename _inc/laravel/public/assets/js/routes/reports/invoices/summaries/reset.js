/**
 * @file Reports Invoice Summary Reset Route Guard
 * @description Guards invoice summary report reset links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("a.reset-invoice-summary", {
      fallbackMsg:
        "Invoice summary report route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (_) {}
})();
