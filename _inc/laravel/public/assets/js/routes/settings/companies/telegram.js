/**
 * @fileoverview Settings Telegram route guard
 * @description Protects Telegram settings form from submission when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindSubmitGuard("#telegram-setting", "IyBFUlJPUg==");
  } catch {}
})();
