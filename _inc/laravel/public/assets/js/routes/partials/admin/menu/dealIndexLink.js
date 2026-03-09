/**
 * @file partials/admin/menu/dealIndexLink.js
 * @description Deals index menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#deals-index-link");
  } catch (err) {
    console.error("Error initializing deals index menu guard:", err);
  }
})();
