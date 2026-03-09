/**
 * @fileoverview Settings reCAPTCHA route guard
 * @description Protects reCAPTCHA settings form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#settings-recaptcha-store-form", "IyBFUlJPUg==");
  } catch {}
})();
