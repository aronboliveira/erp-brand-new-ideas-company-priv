/**
 * @file partials/admin/menu/dashboardViewLink.js
 * @description Dashboard view menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#dashboard-link");
  } catch (err) {
    console.error("Error initializing dashboard view menu guard:", err);
  }
})();
