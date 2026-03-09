/**
 * Labels Index Route Guards
 * Handles label route validation with DOMContentLoaded
 * @module routes/labels/index
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  document.addEventListener("DOMContentLoaded", () => {
    guard.initTooltips();
    guard.bindClickGuard("a[data-guard-msg], a[data-url]");
    guard.bindSubmitGuard("form[data-guard-msg], form[data-url]");
  });
})();
