/**
 * @fileoverview Settings cache store route guard
 * @description Protects cache settings store form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#cache-settings-store-form", "IyBFUlJPUg==");
  } catch {}
})();
