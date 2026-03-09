/**
 * @fileoverview Form submission guard for email template storage using ERPGuard singleton
 * @module assets/js/routes/emailTemplates/store
 */
(() => {
  try {
    window.ERPGuard?.bindSubmitGuard?.("#email-template-store-form", {
      msg: btoa(
        "Store email template route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
