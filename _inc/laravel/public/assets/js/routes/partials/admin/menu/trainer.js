/**
 * @file partials/admin/menu/trainer.js
 * @description Trainer menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#trainer-index-link");
  } catch (err) {
    console.error("Error initializing trainer menu guard:", err);
  }
})();
