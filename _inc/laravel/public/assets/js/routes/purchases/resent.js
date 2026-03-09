/**
 * @file Purchase Resent Route Guard
 * @description Guards resend purchase links using ERPGuard singleton
 */
// assets/js/routes/purchases/resent.js
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("a.resent-purchase", {
      fallbackMsg:
        "Resend purchase route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (_) {}
})();
