/**
 * @file partials/admin/menu/warehouseTransfer.js
 * @description Warehouse transfer menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#warehouse-transfer-index-link");
  } catch (err) {
    console.error("Error initializing warehouse transfer menu guard:", err);
  }
})();
