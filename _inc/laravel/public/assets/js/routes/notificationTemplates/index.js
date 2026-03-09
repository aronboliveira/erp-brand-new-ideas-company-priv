/**
 * Notification Templates Route Guards
 * Handles notification template route validation with DOMContentLoaded
 * @module routes/notificationTemplates/index
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  document.addEventListener("DOMContentLoaded", () => {
    guard.bindClickGuard("a[data-guard-msg],a[data-url]");
    guard.bindSubmitGuard("form[data-guard-msg],form[data-url]");
    guard.initTooltips();
  });
})();
