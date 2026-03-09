/**
 * @fileoverview Click guard for POS barcode back link using ERPGuard singleton
 * @module assets/js/routes/pos/barcode
 */
(() => {
  try {
    window.ERPGuard?.bindClickGuard?.("#pos-barcode-back-link", {
      msg: btoa(
        "POS barcode route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
