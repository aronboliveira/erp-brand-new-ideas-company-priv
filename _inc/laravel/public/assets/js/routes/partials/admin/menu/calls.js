/**
 * @file partials/admin/menu/calls.js
 * @description Communication menu links guard using ERPGuard singleton (3 links)
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#support-system-link");
    window.ERPGuard.bindClickGuard("#zoom-meeting-link");
    window.ERPGuard.bindClickGuard("#messenger-link");
  } catch (err) {
    console.error("Error initializing communication menu guards:", err);
  }
})();
