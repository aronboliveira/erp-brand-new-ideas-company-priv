/**
 * @file partials/admin/menu/client.js
 * @description Client menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#clients-index-link");
  } catch (err) {
    console.error("Error initializing client menu guard:", err);
  }
})();
