/**
 * @file Debit note custom create link route guard
 * @description Prevents navigation if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindClickGuard("#bills-custom-debit-note-create-link", {
      msg: btoa(
        "Create custom debit note route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
