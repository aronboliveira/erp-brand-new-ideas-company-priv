/**
 * @fileoverview Invoice customer add payment receipt route guard
 * @description Protects payment add receipt link from clicks when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindClickGuard(
      '[data-listener-alias="payment-add-receipt"]',
      "IyBFUlJPUg==",
    );
  } catch {}
})();
