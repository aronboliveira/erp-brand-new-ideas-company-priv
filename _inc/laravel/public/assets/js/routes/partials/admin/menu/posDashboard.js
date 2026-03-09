/**
 * @file partials/admin/menu/posDashboard.js
 * @description POS dashboard menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#pos-dashboard-link");
  } catch (err) {
    console.error("Error initializing POS dashboard menu guard:", err);
  }
})();
