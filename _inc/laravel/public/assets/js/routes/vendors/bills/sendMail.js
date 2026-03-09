/**
 * @fileoverview Form submission guard for vendor bill mail sending using ERPGuard singleton
 * @module assets/js/routes/vendors/bills/sendMail
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#vendor-bill-send-mail-form", {
      msg: btoa(
        "Send vendor bill mail route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
