/**
 * @fileoverview Form submission guard for support storage using ERPGuard singleton
 * @module assets/js/routes/supports/store
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#store-support-form", {
      msg: btoa(
        "Store support route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
