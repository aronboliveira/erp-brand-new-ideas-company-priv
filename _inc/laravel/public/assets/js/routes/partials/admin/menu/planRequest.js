/**
 * @file partials/admin/menu/planRequest.js
 * @description Plan request index menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#plan-request-index-link");
  } catch (err) {
    console.error("Error initializing plan request menu guard:", err);
  }
})();
