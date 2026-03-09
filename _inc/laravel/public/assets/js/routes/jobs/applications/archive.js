/**
 * @file Job Application Archive Route Guard
 * @description Guards archive forms and links using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard('form[id^="archive-form-"][data-url][data-guard-msg]', {
    msgKey: 'archive_job_application_unavailable',
    fallbackMsg: 'Archive job application route is unavailable. Please contact technical support or your domain administrator.',
  });

  guard.bindClickGuard('a[id^="archive-link-"][data-url][data-guard-msg]', {
    msgKey: 'archive_job_application_unavailable',
    fallbackMsg: 'Archive job application route is unavailable. Please contact technical support or your domain administrator.',
  });
})();
