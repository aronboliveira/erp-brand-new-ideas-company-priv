/**
 * Goals Index Route Guards
 * Handles goal-related route validation and user feedback
 * @module routes/goals/index
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("a[data-guard-msg], a[data-url]");
  guard.bindSubmitGuard("form[data-guard-msg], form[data-url]");
  guard.initTooltips();
})();
