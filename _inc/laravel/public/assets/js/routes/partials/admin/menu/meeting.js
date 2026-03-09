/**
 * @file partials/admin/menu/meeting.js
 * @description Meeting index menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#meeting-index-link");
  } catch (err) {
    console.error("Error initializing meeting index menu guard:", err);
  }
})();
