/**
 * @file partials/admin/menu/userLink.js
 * @description User index menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#user-index-link");
  } catch (err) {
    console.error("Error initializing user link menu guard:", err);
  }
})();
