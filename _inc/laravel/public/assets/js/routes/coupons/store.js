/**
 * @file Coupon Store Route Guard
 * @description Guards the coupon creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard('#coupon-store-form', {
    msgKey: 'store_coupon_unavailable',
    fallbackMsg: 'Store coupon route is unavailable. Please contact technical support or your domain administrator.',
  });
})();
