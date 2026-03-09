/**
 * Job Board Store Route Guards
 * Handles multiple job onboarding form validation
 * @module routes/jobs/boards/store
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard(
    'form[id^="job-ob-store-form-"][data-url][data-guard-msg]',
    {
      fallbackMsg:
        "Job onboarding route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
