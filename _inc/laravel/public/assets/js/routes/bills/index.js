/**
 * @file Bill index link route guard
 * @description Prevents navigation if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindClickGuard("#bill-index-link", {
      msg: btoa(
        "Bill index route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
