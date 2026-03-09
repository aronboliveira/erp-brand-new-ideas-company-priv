/**
 * @file Allowance Option Edit Route Guard
 * @description Guards the allowance option update form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#allowance-option-update-form", {
    msgKey: "update_allowance_option_unavailable",
    fallbackMsg:
      "Update allowance option route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
