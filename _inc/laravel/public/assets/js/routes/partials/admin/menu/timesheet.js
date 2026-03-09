/**
 * @file partials/admin/menu/timesheet.js
 * @description Timesheet menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#timesheet-list-link");
  } catch (err) {
    console.error("Error initializing timesheet menu guard:", err);
  }
})();
