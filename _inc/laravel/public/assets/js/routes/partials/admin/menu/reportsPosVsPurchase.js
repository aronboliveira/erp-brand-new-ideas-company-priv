/**
 * @file partials/admin/menu/reportsPosVsPurchase.js
 * @description POS vs purchase report menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#pos-vs-purchase-report-link");
  } catch (err) {
    console.error("Error initializing POS vs purchase report menu guard:", err);
  }
})();
