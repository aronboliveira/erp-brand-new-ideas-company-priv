/**
 * @file Transfer Create Route Guard
 * @description Guards the transfer creation link using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard('#transfer-create-link', {
    msgKey: 'create_transfer_unavailable',
    fallbackMsg: 'Create transfer route is unavailable. Please contact technical support or your domain administrator.',
  });
})();
