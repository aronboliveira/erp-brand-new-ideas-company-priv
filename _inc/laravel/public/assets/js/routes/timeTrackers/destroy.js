/**
 * @file Time Tracker Destroy Route Guard
 * @description Guards time tracker delete forms using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard('form[id^="delete-form-"]', {
    msgKey: "destroy_time_tracker_unavailable",
    fallbackMsg:
      "Delete tracker route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
