/**
 * @file partials/admin/menu/timesheetListLink.js
 * @description Timesheet list link menu guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#timesheet-list-link");
  } catch (err) {
    console.error("Error initializing timesheet list link menu guard:", err);
  }
})();
