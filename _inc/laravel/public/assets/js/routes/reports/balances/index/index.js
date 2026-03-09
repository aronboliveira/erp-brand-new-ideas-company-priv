/**
 * Balance Sheet Report Route Guards
 * Handles balance sheet report form validation
 * @module routes/reports/balances/index/index
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#report_bill_summary", {
    fallbackMsg:
      "View balance sheet route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
