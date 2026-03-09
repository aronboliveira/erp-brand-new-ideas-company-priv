/**
 * @file Email Settings Route Guard
 * @description Guards the email settings form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#email-settings-form", {
    msgKey: "email_settings_unavailable",
    fallbackMsg:
      "Email settings route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
