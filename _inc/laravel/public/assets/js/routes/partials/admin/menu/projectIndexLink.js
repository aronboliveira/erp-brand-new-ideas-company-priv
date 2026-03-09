/**
 * @file partials/admin/menu/projectIndexLink.js
 * @description Projects menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#projects-link");
  } catch (err) {
    console.error("Error initializing projects menu guard:", err);
  }
})();
