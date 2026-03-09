/**
 * @file Product/service unit update form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#productServiceUnit-update-form", {
      msg: btoa(
        "Update Product/Service Unit route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
