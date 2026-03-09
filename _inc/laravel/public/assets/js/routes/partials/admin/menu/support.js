/**
 * @file partials/admin/menu/support.js
 * @description Support menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#support-link");
  } catch (err) {
    console.error("Error initializing support menu guard:", err);
  }
})();
