/**
 * Invoices Customers Bank Payment Receipt Route Guards
 * Handles bank payment receipt links
 * @module routes/invoices/customers/bankPaymentReceipt
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard('[data-listener-alias^="bankpayment-receipt"]', {
    fallbackMsg:
      "Bank payment receipt route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
