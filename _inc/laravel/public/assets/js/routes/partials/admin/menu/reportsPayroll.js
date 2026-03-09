/**
 * @file partials/admin/menu/reportsPayroll.js
 * @description Payroll reports menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#reports-payroll-link");
  } catch (err) {
    console.error("Error initializing payroll reports menu guard:", err);
  }
})();
