/**
 * @file partials/admin/menu/accountStatement.js
 * @description Account statement menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#account-statement-link");
  } catch (err) {
    console.error("Error initializing account statement menu guard:", err);
  }
})();
