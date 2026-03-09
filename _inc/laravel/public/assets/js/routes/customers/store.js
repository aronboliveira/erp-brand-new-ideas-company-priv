/**
 * @file Customer Store Route Guard
 * @description Guards the customer creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard('#customers-store-form', {
    msgKey: 'store_customer_unavailable',
    fallbackMsg: 'Store customer route is unavailable. Please contact technical support or your domain administrator.',
  });
})();
