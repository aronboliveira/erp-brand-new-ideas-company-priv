/**
 * @fileoverview Settings role cancel route guard
 * @description Protects role index cancel link from navigation when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindClickGuard("#role-index-cancel-link", "IyBFUlJPUg==");
  } catch {}
})();
