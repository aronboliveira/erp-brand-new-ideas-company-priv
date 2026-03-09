/**
 * Job Application Skill Store Route Guards
 * Handles job application skill store form validation
 * @module routes/jobs/applications/skillStore
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard('form[id^="job-application-skill-store-form-"]', {
    fallbackMsg:
      "Create job application skill route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
