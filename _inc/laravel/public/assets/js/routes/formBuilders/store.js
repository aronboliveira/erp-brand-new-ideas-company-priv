/**
 * @file Form builder store form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#fm-bd-form", {
      msg: btoa(
        "Form route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
