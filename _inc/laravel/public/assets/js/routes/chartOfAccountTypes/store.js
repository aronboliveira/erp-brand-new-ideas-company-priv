/**
 * @file Chart of account type store form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#store_chart_of_account_type", {
      msg: btoa(
        "Chart of account type route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
