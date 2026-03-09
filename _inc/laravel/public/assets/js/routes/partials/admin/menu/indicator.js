/**
 * @file partials/admin/menu/indicator.js
 * @description Indicator index menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#indicator-index-link");
  } catch (err) {
    console.error("Error initializing indicator index menu guard:", err);
  }
})();
