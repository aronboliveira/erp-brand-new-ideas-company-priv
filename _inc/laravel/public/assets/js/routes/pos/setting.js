/**
 * @fileoverview Click guard for POS barcode setting using ERPGuard singleton
 * @module assets/js/routes/pos/setting
 */
(() => {
  try {
    window.ERPGuard?.bindClickGuard?.("#pos-setting-btn", {
      msg: btoa(
        "POS barcode setting route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
