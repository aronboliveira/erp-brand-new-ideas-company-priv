/**
 * @fileoverview Settings Slack route guard
 * @description Protects Slack settings form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#slack-setting", "IyBFUlJPUg==");
  } catch {}
})();
