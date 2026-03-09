/**
 * Other Payment Store Route Guards
 * Handles other payment store form validation
 * @module routes/otherPayments/store
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard("#ot-pay-store-form", {
    fallbackMsg:
      "Other payment store route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
