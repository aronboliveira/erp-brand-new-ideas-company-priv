/**
 * @file partials/admin/menu/warehouse.js
 * @description Warehouse menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#warehouse-index-link");
  } catch (err) {
    console.error("Error initializing warehouse menu guard:", err);
  }
})();
