/**
 * @file Bill summary apply form trigger route guard
 * @description Prevents form submission if route is unavailable using event delegation
 */
(() => {
  try {
    window.ERPGuard.bindClickGuard("a.apply-bill-summary", {
      msg: btoa(
        "Bill summary report route is unavailable. Please contact technical support or your domain administrator.",
      ),
      checkFormTarget: true,
    });
  } catch (_) {}
})();
