/**
 * @file partials/admin/menu/productService.js
 * @description Product services menu link guards using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#product-services-index-link");
    window.ERPGuard.bindClickGuard("#product-stock-index-link");
  } catch (err) {
    console.error("Error initializing product service menu guards:", err);
  }
})();
