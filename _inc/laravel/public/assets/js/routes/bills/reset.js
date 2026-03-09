/**
 * @file Bill summary reset link route guard
 * @description Prevents navigation if route is unavailable using event delegation
 */
(() => {
  try {
    window.ERPGuard.bindClickGuard("a.reset-bill-summary", {
      msg: btoa(
        "Bill summary report route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (_) {}
})();
