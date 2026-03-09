/**
 * @fileoverview Custom question store route guard
 * @description Protects custom question store form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#custom-question-store-form", "IyBFUlJPUg==");
  } catch {}
})();
