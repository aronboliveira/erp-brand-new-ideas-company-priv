/**
 * @file partials/admin/menu/notificationTemplate.js
 * @description Notification template menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#notification-template-index-link");
  } catch (err) {
    console.error("Error initializing notification template menu guard:", err);
  }
})();
