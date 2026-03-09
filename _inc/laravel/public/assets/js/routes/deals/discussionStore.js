/**
 * @fileoverview Deal discussion store route guard
 * @description Protects discussion store form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#discussion-store-form", "IyBFUlJPUg==");
  } catch {}
})();
