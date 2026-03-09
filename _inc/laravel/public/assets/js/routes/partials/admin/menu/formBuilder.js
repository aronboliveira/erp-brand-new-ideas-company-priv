/**
 * @file partials/admin/menu/formBuilder.js
 * @description Form builder menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#form-builder-link");
  } catch (err) {
    console.error("Error initializing form builder menu guard:", err);
  }
})();
