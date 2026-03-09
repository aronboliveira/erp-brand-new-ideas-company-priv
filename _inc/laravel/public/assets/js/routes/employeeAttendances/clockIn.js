/**
 * @fileoverview Form submission guard for employee clock in using ERPGuard singleton
 * @module assets/js/routes/employeeAttendances/clockIn
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#clock-in-form", {
      msg: btoa(
        "Clock in route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
