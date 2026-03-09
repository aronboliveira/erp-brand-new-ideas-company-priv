/**
 * @fileoverview Settings company index route guard
 * @description Protects company payment settings form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#company-payment-settings-form", "IyBFUlJPUg==");
  } catch {}
})();
