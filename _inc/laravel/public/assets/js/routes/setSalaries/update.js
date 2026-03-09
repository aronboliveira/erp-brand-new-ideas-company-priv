/**
 * @fileoverview Form submission guard for employee salary update using ERPGuard singleton
 * @module assets/js/routes/setSalaries/update
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#employee-salary-update-form", {
      msg: btoa("# ERROR"),
    });
  } catch {}
})();
