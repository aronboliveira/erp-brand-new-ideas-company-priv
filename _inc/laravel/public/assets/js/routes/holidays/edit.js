/**
 * @file Holiday edit form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#holiday-edit-form", {
      msg: btoa(
        "Update route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
