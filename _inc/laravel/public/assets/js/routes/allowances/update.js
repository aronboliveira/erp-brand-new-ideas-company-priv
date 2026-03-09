/**
 * @file Allowance Update Route Guard
 * @description Guards the allowance update form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#allowance-update-form", {
    msgKey: "update_allowance_unavailable",
    fallbackMsg:
      "Update allowance route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
