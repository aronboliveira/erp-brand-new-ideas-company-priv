/**
 * @fileoverview Settings test mail route guard
 * @description Protects test email form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#test_email", "IyBFUlJPUg==");
  } catch {}
})();
