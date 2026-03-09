/**
 * @file Budget cancel quarterly button route guard
 * @description Prevents navigation if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindClickGuard("#budget-planner-cancel-btn-quarterly", {
      msg: btoa(
        "Cancel quarterly budget route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
