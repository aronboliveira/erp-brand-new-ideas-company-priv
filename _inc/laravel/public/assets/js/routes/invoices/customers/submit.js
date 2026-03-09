/**
 * Invoices Customers Submit Route Guards
 * Handles customer invoice submission form
 * @module routes/invoices/customers/submit
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#customer_submit", {
    fallbackMsg:
      "Customer invoice submission route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
