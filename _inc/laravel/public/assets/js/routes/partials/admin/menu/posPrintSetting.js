/**
 * @file partials/admin/menu/posPrintSetting.js
 * @description POS print setting menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#pos-print-setting-link");
  } catch (err) {
    console.error("Error initializing POS print setting menu guard:", err);
  }
})();
