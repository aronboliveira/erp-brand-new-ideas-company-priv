/**
 * @file partials/admin/menu/printSetting.js
 * @description Print setting menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#print-setting-link");
  } catch (err) {
    console.error("Error initializing print setting menu guard:", err);
  }
})();
