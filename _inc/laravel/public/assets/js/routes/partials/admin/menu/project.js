/**
 * @file partials/admin/menu/project.js
 * @description Projects index menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#projects-index-link");
  } catch (err) {
    console.error("Error initializing projects index menu guard:", err);
  }
})();
