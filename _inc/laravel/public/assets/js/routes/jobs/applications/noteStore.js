/**
 * Job Application Note Store Route Guards
 * Handles job application note store form validation
 * @module routes/jobs/applications/noteStore
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard('form[id^="job-application-note-store-form-"]', {
    fallbackMsg:
      "Create job application note route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
