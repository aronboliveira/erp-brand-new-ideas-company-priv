/**
 * @file Customer Invoice Route Guard
 * @description Guards invoice show buttons using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard('[id^="invoice-show-btn-"]', {
    msgKey: "show_invoice_unavailable",
    fallbackMsg:
      "Show invoice route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
