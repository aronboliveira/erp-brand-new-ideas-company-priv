/**
 * @file Attendance Store Route Guard
 * @description Guards the employee attendance creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#store-employee-attendance-form", {
    msgKey: "create_employee_attendance_unavailable",
    fallbackMsg:
      "Create employee attendance route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
