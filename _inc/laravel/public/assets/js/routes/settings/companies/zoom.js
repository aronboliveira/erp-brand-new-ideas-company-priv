/**
 * @fileoverview Settings zoom route guard
 * @description Protects zoom settings form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#zoom-settings-form", "IyBFUlJPUg==");
  } catch {}
})();
