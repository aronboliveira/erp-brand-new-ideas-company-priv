/**
 * @file partials/admin/menu/paySlip.js
 * @description Payslip menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#payslip-link");
  } catch (err) {
    console.error("Error initializing payslip menu guard:", err);
  }
})();
