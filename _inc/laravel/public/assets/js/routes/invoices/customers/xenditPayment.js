/**
 * @fileoverview Invoice Xendit payment route guard
 * @description Protects Xendit payment form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#xendit-payment-form", "IyBFUlJPUg==");
  } catch {}
})();
