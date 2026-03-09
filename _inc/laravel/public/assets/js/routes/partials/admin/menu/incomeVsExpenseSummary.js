/**
 * @file partials/admin/menu/incomeVsExpenseSummary.js
 * @description Income vs expense summary menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#income-vs-expense-summary-link");
  } catch (err) {
    console.error(
      "Error initializing income vs expense summary menu guard:",
      err,
    );
  }
})();
