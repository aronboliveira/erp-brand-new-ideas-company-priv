/**
 * @fileoverview Settings Google calendar route guard
 * @description Protects Google calendar settings form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#settings-google-calendar-form", "IyBFUlJPUg==");
  } catch {}
})();
