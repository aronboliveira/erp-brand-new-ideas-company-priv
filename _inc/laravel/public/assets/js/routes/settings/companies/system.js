/**
 * @fileoverview Settings system route guard
 * @description Protects system settings form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#system-settings-form", "IyBFUlJPUg==");
  } catch {}
})();
