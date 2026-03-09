/**
 * @file partials/admin/menu/accountingSetup.js
 * @description Accounting setup menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#accounting-setup-link");
  } catch (err) {
    console.error("Error initializing accounting setup menu guard:", err);
  }
})();
