/**
 * @file partials/admin/menu/setSalary.js
 * @description Set salary menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#set-salary-link");
  } catch (err) {
    console.error("Error initializing set salary menu guard:", err);
  }
})();
