/**
 * @file partials/admin/menu/settings.js
 * @description Settings menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#settings-index-link");
  } catch (err) {
    console.error("Error initializing settings menu guard:", err);
  }
})();
