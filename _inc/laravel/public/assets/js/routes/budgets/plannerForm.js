/**
 * @file Budget planner form route guard
 * @description Prevents submission if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindClickGuard("#budget-planner-form", {
      msg: btoa(
        "Budget planner route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
