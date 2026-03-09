/**
 * @file partials/admin/menu/tracker.js
 * @description Tracker menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#tracker-link");
  } catch (err) {
    console.error("Error initializing tracker menu guard:", err);
  }
})();
