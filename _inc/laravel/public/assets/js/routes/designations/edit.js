/**
 * @file Designation Edit Route Guard
 * @description Guards designation edit links using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard('a[id^="designation-edit-btn-"][data-url][data-guard-msg]', {
    msgKey: 'edit_designation_unavailable',
    fallbackMsg: 'Edit designation route is unavailable. Please contact technical support or your domain administrator.',
  });
})();
