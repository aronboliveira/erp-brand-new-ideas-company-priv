/**
 * @fileoverview Settings IP create route guard
 * @description Protects system IP create button from clicks when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindClickGuard("#system-ip-create-btn", "IyBFUlJPUg==");
  } catch {}
})();
