/**
 * @fileoverview Settings email notification route guard
 * @description Protects email notification settings form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#email-status-language-form", "IyBFUlJPUg==");
  } catch {}
})();
