/**
 * @file partials/admin/menu/job.js
 * @description Job index menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#job-index-link");
  } catch (err) {
    console.error("Error initializing job menu guard:", err);
  }
})();
