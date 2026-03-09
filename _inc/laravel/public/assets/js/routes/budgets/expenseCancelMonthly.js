/**
 * @file Expense cancel monthly button route guard
 * @description Prevents navigation if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindClickGuard("#expense-cancel-btn-monthly", {
      msg: btoa(
        "Cancel monthly expense route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
