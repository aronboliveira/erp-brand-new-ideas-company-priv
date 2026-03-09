/**
 * @file Employee index link route guard
 * @description Prevents navigation if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindClickGuard("#bc-employee-index-link", {
      msg: btoa(
        "Employee index route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
