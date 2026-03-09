/**
 * @file Account Asset Create Route Guard
 * @description Guards account asset creation links using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("a.account-asset-create", {
    msgKey: "create_account_asset_unavailable",
    fallbackMsg:
      "Create account asset route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
