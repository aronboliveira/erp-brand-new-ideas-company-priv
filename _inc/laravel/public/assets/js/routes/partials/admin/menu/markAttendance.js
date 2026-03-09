/**
 * @file partials/admin/menu/markAttendance.js
 * @description Mark attendance menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#mark-attendance-link");
  } catch (err) {
    console.error("Error initializing mark attendance menu guard:", err);
  }
})();
