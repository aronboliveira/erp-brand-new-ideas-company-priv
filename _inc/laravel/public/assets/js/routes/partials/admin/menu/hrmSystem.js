/**
 * @file partials/admin/menu/hrmSystem.js
 * @description HRM system setup menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#hrm-system-setup-link");
  } catch (err) {
    console.error("Error initializing HRM system setup menu guard:", err);
  }
})();
