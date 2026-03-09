/**
 * @fileoverview Custom question create route guard
 * @description Protects custom question create button from action when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindClickGuard("#custom-question-create-btn", "IyBFUlJPUg==");
  } catch {}
})();
