/**
 * @file Debit note custom create form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindSubmitGuard("#bills-custom-debit-note-create-form", {
      msg: btoa(
        "Create custom debit note route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
