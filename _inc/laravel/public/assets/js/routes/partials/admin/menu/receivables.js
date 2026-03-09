/**
 * @file partials/admin/menu/receivables.js
 * @description Receivables menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#receivables-link");
  } catch (err) {
    console.error("Error initializing receivables menu guard:", err);
  }
})();
