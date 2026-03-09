/**
 * Job Category Store Route Guards
 * Handles job category store form validation
 * @module routes/jobs/categories/store
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#jb-cat-store-form", {
    fallbackMsg:
      "Job category store route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
