/**
 * @fileoverview Form submission guard for features update using ERPGuard singleton
 * @module assets/js/routes/features/update
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#features-update-form", {
      msg: btoa(
        "Update Features route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
