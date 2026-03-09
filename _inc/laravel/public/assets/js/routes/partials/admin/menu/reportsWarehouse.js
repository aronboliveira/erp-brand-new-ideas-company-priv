/**
 * @file partials/admin/menu/reportsWarehouse.js
 * @description Warehouse reports menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#warehouse-report-link");
  } catch (err) {
    console.error("Error initializing warehouse reports menu guard:", err);
  }
})();
