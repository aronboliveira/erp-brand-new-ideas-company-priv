/**
 * @fileoverview Form submission guard for stage storage using ERPGuard singleton
 * @module assets/js/routes/stages/store
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#store-stage-form", {
      msg: btoa(
        "Store stage route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
