/**
 * @file partials/admin/menu/plan.js
 * @description Plan index menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#plan-index-link");
  } catch (err) {
    console.error("Error initializing plan index menu guard:", err);
  }
})();
