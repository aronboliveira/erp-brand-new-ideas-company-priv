/**
 * @file Designation create button route guard
 * @description Prevents navigation if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindClickGuard("#designation-create-btn", {
      msg: btoa(
        "Create designation route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
