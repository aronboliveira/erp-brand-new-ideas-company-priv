/**
 * @file partials/admin/menu/salesReport.js
 * @description Sales report menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#sales-report-link");
  } catch (err) {
    console.error("Error initializing sales report menu guard:", err);
  }
})();
