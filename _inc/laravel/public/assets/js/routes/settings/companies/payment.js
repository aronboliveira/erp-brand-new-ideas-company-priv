/**
 * @fileoverview Settings payment route guard
 * @description Protects company payment settings form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#cp-payment-settings-form", "IyBFUlJPUg==");
  } catch {}
})();
