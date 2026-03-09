/**
 * @file Goal type store form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#gl-tp-store-form", {
      msg: btoa(
        "Goal type store route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
