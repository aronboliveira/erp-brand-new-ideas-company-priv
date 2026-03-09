/**
 * @fileoverview Settings email route guard
 * @description Protects email settings form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#cp-email-settings-form", "IyBFUlJPUg==");
  } catch {}
})();
