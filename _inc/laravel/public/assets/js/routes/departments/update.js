/**
 * Department Update Route Guards
 * Handles multiple department update form validation
 * @module routes/departments/update
 */
(() => {
  const guard = window.ERPGuard;
  if (!guard) return;
  guard.bindSubmitGuard(
    'form[id^="department-update-form-"][data-url][data-guard-msg]',
    {
      fallbackMsg:
        "Update department route is unavailable. Please contact technical support or your domain administrator.",
    },
  );
})();
