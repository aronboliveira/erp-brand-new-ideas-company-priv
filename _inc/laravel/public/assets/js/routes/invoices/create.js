/**
 * @file Invoice Create Route Guard
 * @description Guards the invoice creation links using ERPGuard singleton with modal display
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard(".create-invoice-link", {
    msgKey: "create_invoice_unavailable",
    fallbackMsg:
      "Create invoice route is unavailable. Please contact technical support or your domain administrator.",
    alertMode: "modal",
  });
})();
