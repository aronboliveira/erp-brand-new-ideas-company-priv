/**
 * @file Termination store form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#store_termination", {
      msg: btoa(
        "Store termination route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (error) {}
})();
