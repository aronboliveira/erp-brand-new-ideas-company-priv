/**
 * @file partials/admin/menu/employeeAsset.js
 * @description Employee asset setup menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#employees-asset-setup-link");
  } catch (err) {
    console.error("Error initializing employee asset menu guard:", err);
  }
})();
