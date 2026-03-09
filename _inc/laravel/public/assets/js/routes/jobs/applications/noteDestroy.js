/**
 * @file Job Application Note Delete Route Guard
 * @description Guards delete forms and links for job application notes using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard('form[id^="delete-form-"][data-url][data-guard-msg]', {
    msgKey: "delete_job_note_unavailable",
    fallbackMsg:
      "Delete job application note route is unavailable. Please contact technical support or your domain administrator.",
  });

  guard.bindClickGuard('a[id^="delete-note-link-"][data-url][data-guard-msg]', {
    msgKey: "delete_job_note_unavailable",
    fallbackMsg:
      "Delete job application note route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
