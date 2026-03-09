/**
 * @file partials/admin/menu/projectDashboard.js
 * @description Project dashboard menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#project-dashboard-link");
  } catch (err) {
    console.error("Error initializing project dashboard menu guard:", err);
  }
})();
