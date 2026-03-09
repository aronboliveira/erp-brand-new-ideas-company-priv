/**
 * @file Commission Store/Edit Route Guard
 * @description Guards commission form and edit links using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard('#commission-store-form', {
    msgKey: 'store_commission_unavailable',
    fallbackMsg: 'Store commission route is unavailable. Please contact technical support or your domain administrator.',
  });

  guard.bindClickGuard('[id^="commission-edit-"]', {
    msgKey: 'edit_commission_unavailable',
    fallbackMsg: 'Edit commission route is unavailable. Please contact technical support or your domain administrator.',
  });
})();
