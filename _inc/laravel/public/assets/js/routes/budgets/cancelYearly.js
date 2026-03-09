/**
 * @file Budget cancel yearly button route guard
 * @description Prevents navigation if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindClickGuard("#budget-planner-cancel-btn-yearly", {
      msg: btoa(
        "Cancel yearly budget route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
