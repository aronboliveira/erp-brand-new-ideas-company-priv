/**
 * @file POS settings form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#pos-settings-form", {
      msg: btoa(
        "POS template settings route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
