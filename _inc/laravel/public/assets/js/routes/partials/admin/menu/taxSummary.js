/**
 * @file partials/admin/menu/taxSummary.js
 * @description Tax summary menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#tax-summary-link");
  } catch (err) {
    console.error("Error initializing tax summary menu guard:", err);
  }
})();
