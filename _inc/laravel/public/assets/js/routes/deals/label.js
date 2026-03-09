/**
 * @fileoverview Form submission guard for labels storage using ERPGuard singleton
 * @module assets/js/routes/deals/label
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#labels-store-form", {
      msg: btoa("# ERROR"),
    });
  } catch {}
})();
