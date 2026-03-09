/**
 * @file partials/admin/menu/role.js
 * @description Role index menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#role-index-link");
  } catch (err) {
    console.error("Error initializing role index menu guard:", err);
  }
})();
