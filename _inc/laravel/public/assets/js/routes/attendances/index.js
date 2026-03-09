/**
 * @file Employee Attendance Index Route Guard
 * @description Guards attendance filter form and reset links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) {
      
      return;
    }

    guard.bindSubmitGuard("#employeeAttendance_filter", {
      fallbackMsg:
        "Employee attendance index route is unavailable. Please contact technical support or your domain administrator.",
    });

    guard.bindClickGuard(".reset-employee-attendance-link", {
      fallbackMsg:
        "Employee attendance index route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (err) {
    console.error("Error initializing attendances index guard:", err);
  }
})();
