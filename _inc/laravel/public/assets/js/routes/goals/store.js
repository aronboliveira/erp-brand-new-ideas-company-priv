/**
 * @file Goal store form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#gl-store-form", {
      msg: btoa(
        "Goal store route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
