/**
 * @file Interview Schedule Create Route Guard
 * @description Guards interview schedule create links using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard(
    'a[id^="interview-schedule-create-btn-"][data-url][data-guard-msg]',
    {
      msgKey: "create_interview_schedule_unavailable",
      fallbackMsg:
        "Create interview schedule route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
