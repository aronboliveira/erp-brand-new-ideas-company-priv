/**
 * @file partials/admin/menu/bug.js
 * @description Bug view list menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#bug-view-list-link");
  } catch (err) {
    console.error("Error initializing bug view list menu guard:", err);
  }
})();
