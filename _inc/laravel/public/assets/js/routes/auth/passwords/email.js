/**
 * @file Password Email Route Guard
 * @description Guards the password reset email form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#password-email-form", {
    msgKey: "password_reset_email_unavailable",
    fallbackMsg:
      "Send password reset link route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
