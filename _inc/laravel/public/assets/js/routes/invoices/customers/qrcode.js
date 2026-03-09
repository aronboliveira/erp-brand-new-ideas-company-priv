/**
 * Invoices Customers QR Code Route Guards
 * Handles QR code copy link
 * @module routes/invoices/customers/qrcode
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard('[data-listener-alias="qrcode-copy-link"]', {
    fallbackMsg:
      "QR code route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
