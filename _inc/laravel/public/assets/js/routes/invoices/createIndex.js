/**
 * @fileoverview Invoice create index route guard
 * @description Protects invoice breadcrumb link from navigation when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindClickGuard("#breadcrumb-invoice-link", "IyBFUlJPUg==");
  } catch {}
})();
