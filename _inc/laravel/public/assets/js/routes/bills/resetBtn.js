/**
 * @file Bill reset button route guard
 * @description Prevents navigation if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindClickGuard("#bill-reset-btn", {
      msg: btoa(
        "Bill reset route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (e) {}
})();
