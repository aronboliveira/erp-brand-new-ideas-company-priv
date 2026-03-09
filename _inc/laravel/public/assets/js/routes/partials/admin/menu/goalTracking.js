/**
 * @file partials/admin/menu/goalTracking.js
 * @description Goal tracking index menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#goal-tracking-index-link");
  } catch (err) {
    console.error("Error initializing goal tracking menu guard:", err);
  }
})();
