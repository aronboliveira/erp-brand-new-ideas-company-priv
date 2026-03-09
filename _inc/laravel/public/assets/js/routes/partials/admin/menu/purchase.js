/**
 * @file partials/admin/menu/purchase.js
 * @description Purchase index menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#purchase-index-link");
  } catch (err) {
    console.error("Error initializing purchase menu guard:", err);
  }
})();
