/**
 * @file partials/admin/menu/reportsDailyPos.js
 * @description Daily POS report menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#daily-pos-report-link");
  } catch (err) {
    console.error("Error initializing daily POS report menu guard:", err);
  }
})();
