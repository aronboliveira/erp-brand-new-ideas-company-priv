/**
 * @file Project Task Comment File Delete Route Guard
 * @description Guards comment file delete links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("a.delete-comment-file", {
      fallbackMsg:
        "Destroy file for project task comment route is unavailable. Please contact technical support or your domain administrator.",
      validateUrl: true,
    });
  } catch (_) {}
})();
