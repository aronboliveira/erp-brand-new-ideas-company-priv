/**
 * @file partials/admin/menu/bank.js
 * @description Bank menu links guard using ERPGuard singleton (2 links)
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#bank-account-index-link");
    window.ERPGuard.bindClickGuard("#bank-transfer-index-link");
  } catch (err) {
    console.error("Error initializing bank menu guards:", err);
  }
})();
