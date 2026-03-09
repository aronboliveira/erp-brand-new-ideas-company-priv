/**
 * @fileoverview Form submission guard for payables print report using ERPGuard singleton
 * @module assets/js/routes/reports/payables/index/print
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#payables-print-form", {
      msg: btoa(
        "Payables print route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
