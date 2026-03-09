/**
 * @file Bill cancel button route guard
 * @description Prevents navigation if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindClickGuard("#bill-cancel-btn", {
      msg: btoa(
        "Bill cancel route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
