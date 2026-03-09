/**
 * Payslip Type Store Route Guards
 * Handles payslip type store form validation
 * @module routes/payslips/types/store
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#py-slp-tp-store-form", {
    fallbackMsg:
      "Payslip type store route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
