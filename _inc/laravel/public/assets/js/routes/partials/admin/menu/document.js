/**
 * @file partials/admin/menu/document.js
 * @description Document setup menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#document-setup-link");
  } catch (err) {
    console.error("Error initializing document setup menu guard:", err);
  }
})();
