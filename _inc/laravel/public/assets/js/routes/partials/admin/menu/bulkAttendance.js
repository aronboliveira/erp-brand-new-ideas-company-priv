/**
 * @file partials/admin/menu/bulkAttendance.js
 * @description Bulk attendance menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#bulk-attendance-link");
  } catch (err) {
    console.error("Error initializing bulk attendance menu guard:", err);
  }
})();
