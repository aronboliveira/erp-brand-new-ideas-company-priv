/**
 * @file partials/admin/menu/reportsDailyPurchase.js
 * @description Daily purchase report menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#daily-purchase-report-link");
  } catch (err) {
    console.error("Error initializing daily purchase report menu guard:", err);
  }
})();
