/**
 * @fileoverview Invoice YooKassa payment route guard
 * @description Protects YooKassa payment form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#yookassa-payment-form", "IyBFUlJPUg==");
  } catch {}
})();
