/**
 * @fileoverview Invoice bank payment route guard
 * @description Protects bank payment form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#bankPaymentForm", "IyBFUlJPUg==");
  } catch {}
})();
