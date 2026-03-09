/**
 * @file Job OnBoard Create Route Guard
 * @description Guards job onboard creation links using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard(
    'a[id^="job-onboard-create-btn-"][data-url][data-guard-msg]',
    {
      msgKey: "job_onboard_create_unavailable",
      fallbackMsg:
        "Add to Job OnBoard route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
