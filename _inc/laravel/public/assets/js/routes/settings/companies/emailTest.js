/**
 * @fileoverview Settings email test route guard
 * @description Protects send test mail button from clicks when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindClickGuard("#send-test-mail-btn", "IyBFUlJPUg==");
  } catch {}
})();
