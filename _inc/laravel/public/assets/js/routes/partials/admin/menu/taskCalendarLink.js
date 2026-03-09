/**
 * @file partials/admin/menu/taskCalendarLink.js
 * @description Task calendar link menu guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#task-calendar-link");
  } catch (err) {
    console.error("Error initializing task calendar link menu guard:", err);
  }
})();
