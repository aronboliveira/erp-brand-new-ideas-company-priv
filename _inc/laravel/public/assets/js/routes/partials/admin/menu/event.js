/**
 * @file partials/admin/menu/event.js
 * @description Event setup menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#event-setup-link");
  } catch (err) {
    console.error("Error initializing event setup menu guard:", err);
  }
})();
