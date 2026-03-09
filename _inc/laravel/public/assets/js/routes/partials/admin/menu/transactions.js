/**
 * @file partials/admin/menu/transactions.js
 * @description Transactions (cashflow) menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#cashflow-link");
  } catch (err) {
    console.error("Error initializing transactions menu guard:", err);
  }
})();
