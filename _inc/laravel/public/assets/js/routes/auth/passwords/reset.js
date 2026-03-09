/**
 * @file Password Reset Route Guard
 * @description Guards the password reset form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#reset-password-form", {
    msgKey: "reset_password_unavailable",
    fallbackMsg:
      "Reset password route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
