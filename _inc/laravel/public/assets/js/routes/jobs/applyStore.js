/**
 * Job Apply Store Route Guards
 * Handles job apply store form validation
 * @module routes/jobs/applyStore
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard('form[id^="job-apply-store-form-"]', {
    fallbackMsg:
      "Apply data route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
