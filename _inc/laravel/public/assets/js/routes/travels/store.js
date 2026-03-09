/**
 * @file Travel Store Route Guard
 * @description Guards the travel creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard('#create_travel', {
    msgKey: 'store_travel_unavailable',
    fallbackMsg: 'Store travel route is unavailable. Please contact technical support or your domain administrator.',
  });
})();
