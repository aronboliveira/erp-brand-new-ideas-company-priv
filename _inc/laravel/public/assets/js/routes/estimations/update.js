/**
 * @file Estimation update form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#estimate-update-form", {
      msg: btoa(
        "Update estimate route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
