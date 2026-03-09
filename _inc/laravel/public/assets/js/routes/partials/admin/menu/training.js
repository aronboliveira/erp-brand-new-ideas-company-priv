/**
 * @file partials/admin/menu/training.js
 * @description Training menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#training-index-link");
  } catch (err) {
    console.error("Error initializing training menu guard:", err);
  }
})();
