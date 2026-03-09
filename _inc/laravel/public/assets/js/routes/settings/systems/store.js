/**
 * @fileoverview Settings system store route guard
 * @description Protects system store form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#system-store-form", "IyBFUlJPUg==");
  } catch {}
})();
