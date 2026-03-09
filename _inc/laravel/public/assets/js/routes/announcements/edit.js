/**
 * @file Announcement Edit Route Guard
 * @description Guards the announcement update form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#announcement-update-form", {
    msgKey: "update_announcement_unavailable",
    fallbackMsg:
      "Update announcement route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
