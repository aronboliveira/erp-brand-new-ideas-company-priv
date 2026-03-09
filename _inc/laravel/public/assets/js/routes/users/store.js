/**
 * @file User Store Route Guard
 * @description Guards the user creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard('#user-store-form', {
    msgKey: 'store_user_unavailable',
    fallbackMsg: 'Store user route is unavailable. Please contact technical support or your domain administrator.',
  });
})();
