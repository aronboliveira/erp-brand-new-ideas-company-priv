/**
 * @file partials/admin/menu/employee.js
 * @description Employee menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#employee-link");
  } catch (err) {
    console.error("Error initializing employee menu guard:", err);
  }
})();
