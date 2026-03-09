/**
 * @fileoverview Form submission guard for balance sheet print using ERPGuard singleton
 * @module assets/js/routes/reports/balances/horizontal/index/print
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#balance-sheet-print-form", {
      msg: btoa(
        "Print balance sheet route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
