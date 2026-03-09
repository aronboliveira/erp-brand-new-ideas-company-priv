/**
 * @fileoverview Form submission guard for contract type storage using ERPGuard singleton
 * @module assets/js/routes/contractTypes/store
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#contract-type-store-form", {
      msg: btoa("# ERROR"),
    });
  } catch {}
})();
