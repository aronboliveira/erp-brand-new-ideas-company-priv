/**
 * @file partials/admin/menu/contract.js
 * @description Contract index menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#ctc-index-link");
  } catch (err) {
    console.error("Error initializing contract index menu guard:", err);
  }
})();
