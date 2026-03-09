/**
 * @file Termination create link route guard
 * @description Prevents navigation if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindClickGuard("#termination-create-link", {
      msg: btoa(
        "Create termination route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch {}
})();
