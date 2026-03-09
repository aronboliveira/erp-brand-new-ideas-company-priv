/**
 * @file Deduction Option Store Route Guard
 * @description Guards the deduction option creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard('#deduction-option-store-form', {
    msgKey: 'store_deduction_option_unavailable',
    fallbackMsg: 'Store deduction option route is unavailable. Please contact technical support or your domain administrator.',
  });
})();
