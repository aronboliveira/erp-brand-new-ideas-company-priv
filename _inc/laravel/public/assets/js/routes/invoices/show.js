/**
 * @file Invoice Show Route Guard
 * @description Guards the show invoice links using ERPGuard singleton with modal display
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard(".show-invoice-link", {
    msgKey: "show_invoice_unavailable",
    fallbackMsg:
      "Show invoice route is unavailable. Please contact technical support or your domain administrator.",
    alertMode: "modal",
  });
})();
