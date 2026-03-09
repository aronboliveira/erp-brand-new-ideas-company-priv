/**
 * User Profiles Reset Password Route Guards
 * Handles user password update forms with dynamic IDs
 * @module routes/users/profiles/resetPassword
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard('form[id^="user-password-update-form-"]', {
    fallbackMsg:
      "Update user password route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
