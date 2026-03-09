/**
 * @file Account Asset Edit Route Guard
 * @description Guards the account asset edit form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#edit-account-asset-form", {
    msgKey: "update_account_asset_unavailable",
    fallbackMsg:
      "Update account asset route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
