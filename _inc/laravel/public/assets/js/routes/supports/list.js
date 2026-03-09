/**
 * Support List Route Guards
 * Handles support list link validation
 * @module routes/supports/list
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("a.support-list", {
    fallbackMsg:
      "List support route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
