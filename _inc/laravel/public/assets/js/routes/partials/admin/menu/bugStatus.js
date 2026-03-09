/**
 * @file partials/admin/menu/bugStatus.js
 * @description Bug status index menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#bug-status-index-link");
  } catch (err) {
    console.error("Error initializing bug status menu guard:", err);
  }
})();
