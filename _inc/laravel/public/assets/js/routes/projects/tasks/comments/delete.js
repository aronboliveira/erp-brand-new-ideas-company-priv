/**
 * @file Project Task Comment Delete Route Guard
 * @description Guards task comment delete links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("a.delete-comment", {
      fallbackMsg:
        "Destroy project task comment route is unavailable. Please contact technical support or your domain administrator.",
      validateUrl: true,
    });
  } catch (_) {}
})();
