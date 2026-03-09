/**
 * @file Revenue apply form trigger route guard
 * @description Prevents form submission if route is unavailable using checkFormTarget
 */
(() => {
  try {
    window.ERPGuard.bindClickGuard("a.apply-revenue", {
      msg: btoa(
        "Apply revenue route is unavailable. Please contact technical support or your domain administrator.",
      ),
      checkFormTarget: true,
    });
  } catch (err) {}
})();
