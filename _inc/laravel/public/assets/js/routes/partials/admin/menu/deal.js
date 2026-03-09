/**
 * @file partials/admin/menu/deal.js
 * @description Deal index menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#dl-index-link");
  } catch (err) {
    console.error("Error initializing deal index menu guard:", err);
  }
})();
