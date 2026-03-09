/**
 * @fileoverview Settings time tracker route guard
 * @description Protects time tracker settings form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#time-trackers-settings-form", "IyBFUlJPUg==");
  } catch {}
})();
