/**
 * @fileoverview Deal kanban route guard
 * @description Protects deal kanban button from action when route is unavailable
 */

(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;
    guard.bindClickGuard("#deal-kanban-btn", "IyBFUlJPUg==");
  } catch {}
})();
