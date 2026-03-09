/**
 * @fileoverview Invoice Stripe payment route guard
 * @description Protects Stripe payment form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#stripe-payment-form", "IyBFUlJPUg==");
  } catch {}
})();
