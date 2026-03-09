/**
 * @file Invoice Product Route Guard
 * @description Guards the invoice product select elements using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindChangeGuard(
    "select.invoice-product-select[data-url][data-guard-msg]",
    {
      msgKey: "invoice_product_unavailable",
      fallbackMsg:
        "Invoice product route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
