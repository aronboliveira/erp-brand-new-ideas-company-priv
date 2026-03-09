/**
 * @file partials/admin/menu/order.js
 * @description Order index menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#order-index-link");
  } catch (err) {
    console.error("Error initializing order index menu guard:", err);
  }
})();
