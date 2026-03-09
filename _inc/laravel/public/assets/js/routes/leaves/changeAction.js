/**
 * @fileoverview Form submission guard for leave action change using ERPGuard singleton
 * @module assets/js/routes/leaves/changeAction
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#leave-changeaction-form", {
      msg: btoa(
        "Change leave action route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
