/**
 * @file partials/admin/menu/appraisal.js
 * @description Appraisal index menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#appraisal-index-link");
  } catch (err) {
    console.error("Error initializing appraisal menu guard:", err);
  }
})();
