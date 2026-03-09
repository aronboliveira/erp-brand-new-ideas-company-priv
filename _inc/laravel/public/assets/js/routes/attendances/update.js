/**
 * @file Attendance Update Route Guard
 * @description Guards the employee attendance update form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#update-employee-attendance-form", {
    msgKey: "update_employee_attendance_unavailable",
    fallbackMsg:
      "Update employee attendance route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
