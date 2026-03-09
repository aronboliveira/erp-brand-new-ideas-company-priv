/**
 * @file Transfer Store Route Guard
 * @description Guards the transfer creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard('#create_transfer', {
    msgKey: 'store_transfer_unavailable',
    fallbackMsg: 'Store transfer route is unavailable. Please contact technical support or your domain administrator.',
  });
})();
