/**
 * @file Allowance Option Store Route Guard
 * @description Guards the allowance option creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#allowance-option-store-form", {
    msgKey: "store_allowance_option_unavailable",
    fallbackMsg:
      "Create allowance option route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
