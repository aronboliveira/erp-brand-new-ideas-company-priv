/**
 * @fileoverview Product category create route guard
 * @description Protects product category create button from action when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindClickGuard("#product-category-create-btn", "IyBFUlJPUg==");
  } catch {}
})();
