/**
 * @fileoverview Settings company store route guard
 * @description Protects company settings form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#cp-settings-form", "IyBFUlJPUg==");
  } catch {}
})();
