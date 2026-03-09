/**
 * @fileoverview Deal store index route guard
 * @description Protects clients index link from navigation when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindClickGuard("#clients-index-link", "IyBFUlJPUg==");
  } catch {}
})();
