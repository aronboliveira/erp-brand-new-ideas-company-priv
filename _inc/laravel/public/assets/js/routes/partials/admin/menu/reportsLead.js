/**
 * @file partials/admin/menu/reportsLead.js
 * @description Lead reports menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#reports-lead-link");
  } catch (err) {
    console.error("Error initializing lead reports menu guard:", err);
  }
})();
