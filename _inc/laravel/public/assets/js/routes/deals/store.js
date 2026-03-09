/**
 * @fileoverview Form submission guard for deal call storage using ERPGuard singleton
 * @module assets/js/routes/deals/store
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#deal-call-store-form", {
      msg: btoa("# ERROR"),
    });
  } catch {}
})();
