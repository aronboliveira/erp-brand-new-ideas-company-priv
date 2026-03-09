/**
 * @file Travel Update Route Guard
 * @description Guards the travel update form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard('#edit_travel', {
    msgKey: 'update_travel_unavailable',
    fallbackMsg: 'Update travel route is unavailable. Please contact technical support or your domain administrator.',
  });
})();
