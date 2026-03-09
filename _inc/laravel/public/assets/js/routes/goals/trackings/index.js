/**
 * Goal Trackings Route Guards
 * Handles goal tracking route validation and user feedback
 * @module routes/goals/trackings/index
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("a[data-guard-msg], a[data-url]");
  guard.bindSubmitGuard("form[data-guard-msg], form[data-url]");
  guard.initTooltips();
})();
