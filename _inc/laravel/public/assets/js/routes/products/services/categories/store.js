/**
 * @file Product category save form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#product-category-save-form", {
      msg: btoa(
        "Product category save route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
