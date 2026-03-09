/**
 * @file partials/admin/menu/chart.js
 * @description Chart/accounting menu links guard using ERPGuard singleton (6 links)
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#chart-of-accounts-link");
    window.ERPGuard.bindClickGuard("#journal-account-link");
    window.ERPGuard.bindClickGuard("#ledger-summary-link");
    window.ERPGuard.bindClickGuard("#balance-sheet-link");
    window.ERPGuard.bindClickGuard("#profit-loss-link");
    window.ERPGuard.bindClickGuard("#trial-balance-link");
  } catch (err) {
    console.error("Error initializing chart menu guards:", err);
  }
})();
