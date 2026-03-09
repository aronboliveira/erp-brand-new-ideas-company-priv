/**
 * @file Customer Import Route Guard
 * @description Guards the customer CSV import form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#customer-csv-import-form", {
    msgKey: "import_customer_unavailable",
    fallbackMsg:
      "Import customer route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
