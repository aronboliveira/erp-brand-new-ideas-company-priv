/**
 * Leave Create Route Guards
 * Handles leave creation link validation
 * @module routes/leaves/create
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindClickGuard("#leave-create-link", {
    fallbackMsg:
      "Create leave route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
