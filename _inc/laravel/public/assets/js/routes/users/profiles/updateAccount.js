/**
 * @fileoverview Form submission guard for profile account update using ERPGuard singleton
 * @module assets/js/routes/users/profiles/updateAccount
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#profile-account-update-form", {
      msg: btoa(
        "Update account route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
