/**
 * @fileoverview Settings webhook route guard
 * @description Protects webhook create button from clicks when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindClickGuard("#webhook-create-btn", "IyBFUlJPUg==");
  } catch {}
})();
