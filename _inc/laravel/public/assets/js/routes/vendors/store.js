/**
 * @file Vendor Store Route Guard
 * @description Guards the vendor creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard('#vendor-store-form', {
    msgKey: 'store_vendor_unavailable',
    fallbackMsg: 'Store vendor route is unavailable. Please contact technical support or your domain administrator.',
  });
})();
