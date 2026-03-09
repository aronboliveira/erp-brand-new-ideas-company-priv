/**
 * @file partials/admin/menu/projectReportLink.js
 * @description Project report menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#project-report-index-link");
  } catch (err) {
    console.error("Error initializing project report menu guard:", err);
  }
})();
