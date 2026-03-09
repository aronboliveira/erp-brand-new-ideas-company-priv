/**
 * Tasks Project Route Guards
 * Handles project index back button
 * @module routes/tasks/project
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#project-index-back-btn", {
    fallbackMsg:
      "Project index route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
