/**
 * @file partials/admin/menu/bugsLink.js
 * @description Bugs menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#bugs-link");
  } catch (err) {
    console.error("Error initializing bugs menu guard:", err);
  }
})();
