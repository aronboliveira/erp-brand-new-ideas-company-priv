/**
 * @file Loan Store/Edit Route Guard
 * @description Guards loan form and edit links using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard('#loan-store-form', {
    msgKey: 'store_loan_unavailable',
    fallbackMsg: 'Store loan route is unavailable. Please contact technical support or your domain administrator.',
  });

  guard.bindClickGuard('[id^="loan-edit-"]', {
    msgKey: 'edit_loan_unavailable',
    fallbackMsg: 'Edit loan route is unavailable. Please contact technical support or your domain administrator.',
  });
})();
