/**
 * @file Reports Horizontal Balance Sheet Reset Route Guard
 * @description Guards horizontal balance sheet report reset links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("a.balance-sheet-reset", {
      fallbackMsg:
        "View balance sheet route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (_) {}
})();
