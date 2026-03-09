/**
 * @file Projects Reports Show Route Guard
 * @description Guards project report show links using ERPGuard singleton
 */
(() => {
  try {
    const guard = window.ERPGuard;
    if (!guard) return;

    guard.bindClickGuard(".show-project-report-link", {
      fallbackMsg:
        "View project report route is unavailable. Please contact technical support or your domain administrator.",
    });
  } catch (_) {}
})();
