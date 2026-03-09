/**
 * @file Termination Type Create Route Guard
 * @description Guards termination type creation link using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("#terminationtype-create-link", {
      fallbackMsg:
        "Create termination type route is unavailable. Please contact technical support or your domain administrator.",
      validateUrl: true,
      updateHref: true,
    });
  } catch (_) {}
})();
