/**
 * @file partials/admin/menu/expenseSummary.js
 * @description Expense summary menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#expense-summary-link");
  } catch (err) {
    console.error("Error initializing expense summary menu guard:", err);
  }
})();
