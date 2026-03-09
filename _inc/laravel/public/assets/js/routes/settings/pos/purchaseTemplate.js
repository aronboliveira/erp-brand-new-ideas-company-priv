/**
 * @file Purchase template settings form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#prc-settings-form", {
      msg: btoa(
        "Purchase template settings route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
