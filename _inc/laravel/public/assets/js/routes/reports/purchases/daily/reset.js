/**
 * @file Reports Purchase Daily Reset Route Guard
 * @description Guards daily purchase report reset links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("a.reset-daily-purchase-link", {
      fallbackMsg:
        "Daily purchase reset route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (_) {}
})();
