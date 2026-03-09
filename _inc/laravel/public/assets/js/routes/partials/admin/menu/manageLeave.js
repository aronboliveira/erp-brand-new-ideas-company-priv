/**
 * @file partials/admin/menu/manageLeave.js
 * @description Manage leave menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#manage-leave-link");
  } catch (err) {
    console.error("Error initializing manage leave menu guard:", err);
  }
})();
