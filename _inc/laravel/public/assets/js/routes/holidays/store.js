/**
 * @file Holiday store form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#hld-store-form", {
      msg: btoa(
        "Holiday store route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
