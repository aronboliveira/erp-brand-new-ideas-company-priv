/**
 * @file Estimation store form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#estimate-store-form", {
      msg: btoa(
        "Store estimate route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
