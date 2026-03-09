/**
 * @file partials/admin/menu/incomeSummary.js
 * @description Income summary menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#income-summary-link");
  } catch (err) {
    console.error("Error initializing income summary menu guard:", err);
  }
})();
