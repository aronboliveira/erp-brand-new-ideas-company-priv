/**
 * @fileoverview Deal create list route guard
 * @description Protects deal create button from action when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindClickGuard("#deal-create-btn", "IyBFUlJPUg==");
  } catch {}
})();
