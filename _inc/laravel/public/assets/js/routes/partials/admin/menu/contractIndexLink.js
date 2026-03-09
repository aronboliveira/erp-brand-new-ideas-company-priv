/**
 * @file partials/admin/menu/contractIndexLink.js
 * @description Contracts index menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#contracts-index-link");
  } catch (err) {
    console.error("Error initializing contracts index menu guard:", err);
  }
})();
