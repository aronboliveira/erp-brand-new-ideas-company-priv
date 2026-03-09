/**
 * @file partials/admin/menu/dashboard.js
 * @description Dashboard menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#dashboard-link");
  } catch (err) {
    console.error("Error initializing dashboard menu guard:", err);
  }
})();
