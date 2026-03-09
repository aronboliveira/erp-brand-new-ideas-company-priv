/**
 * @file Account Asset Store Route Guard
 * @description Guards the account asset creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#store-account-asset-form", {
    msgKey: "store_account_asset_unavailable",
    fallbackMsg:
      "Store account asset route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
