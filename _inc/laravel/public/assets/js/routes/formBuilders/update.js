/**
 * @file Form builder update form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#fm-bd-update-form", {
      msg: btoa(
        "Form update route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
