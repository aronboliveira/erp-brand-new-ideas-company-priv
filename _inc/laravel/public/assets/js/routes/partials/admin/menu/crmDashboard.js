/**
 * @file partials/admin/menu/crmDashboard.js
 * @description CRM dashboard menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#crm-dashboard-link");
  } catch (err) {
    console.error("Error initializing CRM dashboard menu guard:", err);
  }
})();
