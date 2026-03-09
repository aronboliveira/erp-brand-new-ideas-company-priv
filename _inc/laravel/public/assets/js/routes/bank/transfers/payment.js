/**
 * @fileoverview Form submission guard for bank transfer payment using ERPGuard singleton
 * @module assets/js/routes/bank/transfers/payment
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#submit_form", {
      msg: btoa("# ERROR"),
    });
  } catch {}
})();
