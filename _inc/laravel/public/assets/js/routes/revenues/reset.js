/**
 * Revenue Reset Route Guards
 * Handles revenue reset links
 * @module routes/revenues/reset
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("a.reset-revenue", {
    fallbackMsg:
      "Reset revenue route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
