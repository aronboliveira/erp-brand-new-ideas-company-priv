/**
 * @file System settings link route guard
 * @description Prevents navigation if route is unavailable
 */
(() => {
  try {
    window.ERPGuard.bindClickGuard("#system-setting-link", {
      msg: btoa(
        "System Setting route is unavailable. Please contact technical support or your domain administrator.",
      ),
    });
  } catch (err) {}
})();
