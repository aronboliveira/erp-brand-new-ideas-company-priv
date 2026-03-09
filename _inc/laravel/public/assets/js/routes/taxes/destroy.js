/**
 * @file Tax Destroy Route Guard
 * @description Guards tax delete forms using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard('form[id^="delete-form-"]', {
    msgKey: 'destroy_tax_unavailable',
    fallbackMsg: 'Delete tax route is unavailable. Please contact technical support or your domain administrator.',
  });
})();
