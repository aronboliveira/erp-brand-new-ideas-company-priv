/**
 * @file partials/admin/menu/crmSystem.js
 * @description CRM system setup menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#crm-system-setup-link");
  } catch (err) {
    console.error("Error initializing CRM system setup menu guard:", err);
  }
})();
