/**
 * @file Expense cancel half-yearly button route guard
 * @description Prevents navigation if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindClickGuard("#expense-cancel-btn-half-yearly", {
      msg: btoa(
        "Cancel half-yearly expense route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
