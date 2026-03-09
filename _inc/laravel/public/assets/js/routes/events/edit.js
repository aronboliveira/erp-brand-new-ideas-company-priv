/**
 * @file Event Edit Route Guard
 * @description Guards the event edit form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#edit_event_form", {
    msgKey: "edit_event_unavailable",
    fallbackMsg:
      "Edit event route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
