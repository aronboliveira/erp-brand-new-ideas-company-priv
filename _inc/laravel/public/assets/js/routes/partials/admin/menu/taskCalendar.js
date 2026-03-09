/**
 * @file partials/admin/menu/taskCalendar.js
 * @description Task calendar menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#task-calendar-link");
  } catch (err) {
    console.error("Error initializing task calendar menu guard:", err);
  }
})();
