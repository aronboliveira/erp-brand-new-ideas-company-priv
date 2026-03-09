/**
 * @file partials/admin/menu/invoiceSummary.js
 * @description Invoice summary menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#invoice-summary-link");
  } catch (err) {
    console.error("Error initializing invoice summary menu guard:", err);
  }
})();
