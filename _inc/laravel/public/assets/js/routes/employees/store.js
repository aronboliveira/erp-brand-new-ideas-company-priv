/**
 * @file Employee Store Route Guard
 * @description Guards the employee store form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#employee-store-form", {
    msgKey: "store_employee_unavailable",
    fallbackMsg:
      "Store employee route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
