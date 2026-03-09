/**
 * @fileoverview Invoice customer payment receipt route guard
 * @description Protects payment receipt link from clicks when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindClickGuard(
      '[data-listener-alias="payment-receipt"]',
      "IyBFUlJPUg==",
    );
  } catch {}
})();
