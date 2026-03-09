/**
 * @file Reports Ledger Reset Route Guard
 * @description Guards ledger report reset links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("a.reset-ledger", {
      fallbackMsg:
        "Ledger report route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (_) {}
})();
