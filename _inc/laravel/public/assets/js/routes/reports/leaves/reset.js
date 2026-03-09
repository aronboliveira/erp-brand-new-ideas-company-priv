/**
 * @file Reports Leave Reset Route Guard
 * @description Guards leave report reset links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard("a.reset-leave-report", {
      fallbackMsg:
        "Leave report route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (_) {}
})();
