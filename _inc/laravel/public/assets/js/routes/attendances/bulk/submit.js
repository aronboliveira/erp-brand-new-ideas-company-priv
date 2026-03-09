/**
 * @file Attendance Bulk Submit Route Guard
 * @description Guards the bulk attendance submission form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#bulkattendance_post", {
    msgKey: "submit_bulk_attendance_unavailable",
    fallbackMsg:
      "Submit bulk attendance route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
