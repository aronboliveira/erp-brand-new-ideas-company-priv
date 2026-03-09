/**
 * @file Form builder field store form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#fm-fd-store-form", {
      msg: btoa(
        "Form field store route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
