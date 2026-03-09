/**
 * @file Indicators store form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#ind-store-form", {
      msg: btoa(
        "Indicators store route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
