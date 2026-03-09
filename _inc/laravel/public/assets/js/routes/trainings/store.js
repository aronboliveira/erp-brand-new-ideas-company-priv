/**
 * @file Training Store Route Guard
 * @description Guards the training creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard('#create_training', {
    msgKey: 'store_training_unavailable',
    fallbackMsg: 'Store training route is unavailable. Please contact technical support or your domain administrator.',
  });
})();
