/**
 * @file Form builder view store form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#fm-view-store-form", {
      msg: btoa(
        "Form submission route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
