/**
 * @file Form builder field update form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#fm-fd-update-form", {
      msg: btoa(
        "Form field update route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
