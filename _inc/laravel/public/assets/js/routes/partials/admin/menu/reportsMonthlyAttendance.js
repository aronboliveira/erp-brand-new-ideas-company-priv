/**
 * @file partials/admin/menu/reportsMonthlyAttendance.js
 * @description Monthly attendance reports menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#reports-monthly-attendance-link");
  } catch (err) {
    console.error(
      "Error initializing monthly attendance reports menu guard:",
      err,
    );
  }
})();
