/**
 * @fileoverview Settings business route guard
 * @description Protects business settings form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#business-setting-form", "IyBFUlJPUg==");
  } catch {}
})();
