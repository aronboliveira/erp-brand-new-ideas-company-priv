/**
 * @file partials/admin/menu/reportsDeal.js
 * @description Deal reports menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#reports-deal-link");
  } catch (err) {
    console.error("Error initializing deal reports menu guard:", err);
  }
})();
