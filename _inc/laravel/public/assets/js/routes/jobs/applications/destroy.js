/**
 * @file Job Application Delete Route Guard
 * @description Guards delete forms and links using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard('form[id^="delete-form-"][data-url][data-guard-msg]', {
    msgKey: "delete_job_application_unavailable",
    fallbackMsg:
      "Delete job application route is unavailable. Please contact technical support or your domain administrator.",
  });

  guard.bindClickGuard('a[id^="delete-link-"][data-url][data-guard-msg]', {
    msgKey: "delete_job_application_unavailable",
    fallbackMsg:
      "Delete job application route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
