/**
 * Horizontal Balance Sheet Export Route Guards
 * Handles horizontal balance sheet export form validation
 * @module routes/reports/balances/horizontal/index/export
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#balance-sheet-export-form", {
    fallbackMsg:
      "Export balance sheet route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
