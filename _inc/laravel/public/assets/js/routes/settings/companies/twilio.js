/**
 * @fileoverview Settings Twilio route guard
 * @description Protects Twilio settings form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#twilio-setting-form", "IyBFUlJPUg==");
  } catch {}
})();
