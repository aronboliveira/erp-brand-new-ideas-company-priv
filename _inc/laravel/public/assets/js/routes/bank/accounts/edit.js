/**
 * @file Bank Account Edit Route Guard
 * @description Guards the bank account update form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#bank-account-update-form", {
    msgKey: "update_bank_account_unavailable",
    fallbackMsg:
      "Update bank account route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
