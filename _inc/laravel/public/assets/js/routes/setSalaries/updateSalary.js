/**
 * @fileoverview Form submission guard for salary update using ERPGuard singleton
 * @module assets/js/routes/setSalaries/updateSalary
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#salary-update-form", {
      msg: btoa("# ERROR"),
    });
  } catch {}
})();
