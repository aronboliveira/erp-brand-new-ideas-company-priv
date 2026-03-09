/**
 * @file partials/admin/menu/billSummary.js
 * @description Bill summary menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#bill-summary-link");
  } catch (err) {
    console.error("Error initializing bill summary menu guard:", err);
  }
})();
