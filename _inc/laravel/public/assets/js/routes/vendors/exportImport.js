/**
 * @file Vendors Export/Import Route Guard
 * @description Guards vendor import and export links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    const fallbackMsg =
      "Requested route is unavailable. Please contact technical support or your domain administrator.";

    guard.bindClickGuard("#vendor-import", {
      fallbackMsg,
      validateUrl: true,
    });

    guard.bindClickGuard("#vendor-export", {
      fallbackMsg,
      validateUrl: true,
    });
  } catch (_) {}
})();
