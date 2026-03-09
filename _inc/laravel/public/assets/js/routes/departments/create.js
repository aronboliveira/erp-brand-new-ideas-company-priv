/**
 * @file Department create button route guard
 * @description Prevents navigation if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindClickGuard("#department-create-btn", {
      msg: btoa(
        "Create department route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
