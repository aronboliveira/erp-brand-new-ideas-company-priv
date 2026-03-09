/**
 * @file POS Daily Reset Route Guard
 * @description Guards daily POS reset links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard(".reset-daily-pos-link", {
      fallbackMsg:
        "Daily POS reset route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (_) {}
})();
