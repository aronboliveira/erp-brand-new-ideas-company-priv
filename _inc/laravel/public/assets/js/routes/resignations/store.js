/**
 * @file Resignation Store Route Guard
 * @description Guards the resignation creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard('#store_resignation', {
    msgKey: 'store_resignation_unavailable',
    fallbackMsg: 'Store resignation route is unavailable. Please contact technical support or your domain administrator.',
  });
})();
