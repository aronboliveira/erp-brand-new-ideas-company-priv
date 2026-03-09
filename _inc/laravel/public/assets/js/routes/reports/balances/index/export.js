/**
 * Balance Sheet Export Route Guards
 * Handles balance sheet export form validation
 * @module routes/reports/balances/index/export
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#balance-sheet-export-form", {
    fallbackMsg:
      "Export balance sheet route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
