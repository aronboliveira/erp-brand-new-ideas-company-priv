/**
 * @file Chart of Accounts Store Route Guard
 * @description Guards the chart of accounts creation form using ERPGuard singleton
 */

(() => {
  const guard = window.ERPGuard;
  if (!guard) return;

  guard.bindSubmitGuard("#chart_of_accounts_store_form", {
    msgKey: "store_chart_of_accounts_unavailable",
    fallbackMsg:
      "Create chart of accounts route is unavailable. Please contact technical support or your domain administrator.",
  });
})();
