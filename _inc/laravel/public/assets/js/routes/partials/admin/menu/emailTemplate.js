/**
 * @file partials/admin/menu/emailTemplate.js
 * @description Email template menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#email-template-link");
  } catch (err) {
    console.error("Error initializing email template menu guard:", err);
  }
})();
