/**
 * Job Application Store Route Guards
 * Handles job application store form validation
 * @module routes/jobs/applications/store
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#job-app-store-form", {
    fallbackMsg:
      "Job application route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
