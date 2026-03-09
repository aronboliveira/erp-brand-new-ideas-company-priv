/**
 * @fileoverview Form submission guard for tax creation using ERPGuard singleton
 * @module assets/js/routes/taxes/create
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#create-tax-form", {
      msg: btoa(
        "Create tax route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
