/**
 * @file Product/service unit store form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#prd-sv-unt-store-form", {
      msg: btoa(
        "Product/service unit store route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
