/**
 * @file partials/admin/menu/lead.js
 * @description Lead index menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#ld-index-link");
  } catch (err) {
    console.error("Error initializing lead index menu guard:", err);
  }
})();
