/**
 * @fileoverview Form submission guard for feature storage using ERPGuard singleton
 * @module assets/js/routes/features/store
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#feature-store-form", {
      msg: btoa(
        "Store feature route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
