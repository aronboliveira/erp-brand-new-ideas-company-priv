/**
 * @file partials/admin/menu/reportsLeave.js
 * @description Leave reports menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#reports-leave-link");
  } catch (err) {
    console.error("Error initializing leave reports menu guard:", err);
  }
})();
