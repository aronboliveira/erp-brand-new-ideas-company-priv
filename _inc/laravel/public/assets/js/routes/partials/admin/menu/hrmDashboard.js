/**
 * @file partials/admin/menu/hrmDashboard.js
 * @description HRM dashboard menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#hrm-dashboard-link");
  } catch (err) {
    console.error("Error initializing HRM dashboard menu guard:", err);
  }
})();
