/**
 * @fileoverview Form submission guard for meeting update using ERPGuard singleton
 * @module assets/js/routes/meetings/update
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#meeting-update-form", {
      msg: btoa(
        "Update route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
