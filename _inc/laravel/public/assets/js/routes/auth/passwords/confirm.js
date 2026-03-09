/**
 * @file Password Confirm Route Guard
 * @description Guards the password confirmation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#confirm-password-form", {
    msgKey: "confirm_password_unavailable",
    fallbackMsg:
      "Confirm password route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
