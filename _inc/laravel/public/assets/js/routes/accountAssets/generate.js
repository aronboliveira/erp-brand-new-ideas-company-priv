/**
 * @file Account Asset Generate Route Guard
 * @description Guards the account asset generation link using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindClickGuard("#account-asset-generate-link", {
    msgKey: "generate_account_asset_unavailable",
    fallbackMsg:
      "Generate account asset route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
