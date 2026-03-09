/**
 * @fileoverview Form submission guard for employee clock out using ERPGuard singleton
 * @module assets/js/routes/employeeAttendances/clockOut
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#clock-out-form", {
      msg: btoa(
        "Clock out route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
