/**
 * @file partials/admin/menu/pos.js
 * @description POS menu links guard using ERPGuard singleton (2 links)
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#pos-add-index-link");
    window.ERPGuard.bindClickGuard("#pos-report-index-link");
  } catch (err) {
    console.error("Error initializing POS menu guards:", err);
  }
})();
