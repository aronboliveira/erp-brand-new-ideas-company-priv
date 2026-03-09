/**
 * @file partials/admin/menu/systemSettings.js
 * @description System settings menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#system-settings-link");
  } catch (err) {
    console.error("Error initializing system settings menu guard:", err);
  }
})();
