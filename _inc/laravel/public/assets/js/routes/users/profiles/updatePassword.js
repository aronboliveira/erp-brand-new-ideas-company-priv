/**
 * @fileoverview Form submission guard for profile password update using ERPGuard singleton
 * @module assets/js/routes/users/profiles/updatePassword
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#profile-password-update-form", {
      msg: btoa(
        "Update password route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
