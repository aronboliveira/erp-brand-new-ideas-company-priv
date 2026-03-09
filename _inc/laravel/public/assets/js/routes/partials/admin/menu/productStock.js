/**
 * @file partials/admin/menu/productStock.js
 * @description Product stock menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#product-stock-link");
  } catch (err) {
    console.error("Error initializing product stock menu guard:", err);
  }
})();
