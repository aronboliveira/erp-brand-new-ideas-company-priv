/**
 * @fileoverview Form submission guard for resignation update using ERPGuard singleton
 * @module assets/js/routes/resignations/update
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#edit_resignation", {
      msg: btoa(
        "Update resignation route is unavailable. Please contact technical support or your domain administrator.",
      ),
      msgAttr: "data-form-guard-msg",
    });
  } catch {}
})();
