/**
 * @file Chart of account create link route guard
 * @description Prevents navigation if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindClickGuard("#coa-create-account-link", {
      msg: btoa(
        "Create chart of account route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
