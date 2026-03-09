/**
 * @file partials/admin/menu/posBarcode.js
 * @description POS barcode menu link guard using ERPGuard singleton
 */
(() => {
  try {
    if (!window.ERPGuard) {
      
      return;
    }
    window.ERPGuard.bindClickGuard("#pos-barcode-link");
  } catch (err) {
    console.error("Error initializing POS barcode menu guard:", err);
  }
})();
