/**
 * @file partials/admin/menu/payables.js
 * @description Payables menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#payables-link");
  } catch (err) {
    console.error("Error initializing payables menu guard:", err);
  }
})();
